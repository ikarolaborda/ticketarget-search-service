<?php

declare(strict_types=1);

namespace App\Infrastructure\Elasticsearch;

use App\Domain\Search\EventHit;
use App\Domain\Search\SearchCriteria;
use App\Domain\Search\SearchEventsPort;
use App\Domain\Search\SearchResults;
use Elastic\Elasticsearch\Client;

/**
 * Elasticsearch adapter for the search port. Typo tolerance comes from
 * `fuzziness: AUTO`; stemming and accent folding come from the `event_text`
 * analyzer defined on the index. Cross-field matching lets a query hit name,
 * artist, description, or venue in one pass, kept well under the 500ms budget.
 */
final readonly class ElasticsearchEventSearch implements SearchEventsPort
{
    public function __construct(
        private Client $client,
        private string $index,
    ) {
    }

    public function search(SearchCriteria $criteria): SearchResults
    {
        $keyword = trim($criteria->keyword);

        // Empty keyword = browse-all: match every event, ordered by date so the
        // landing page can paginate the full catalog. A keyword switches to a
        // typo-tolerant, stemmed relevance search.
        $must = $keyword === ''
            ? [['match_all' => (object) []]]
            : [[
                'multi_match' => [
                    'query' => $keyword,
                    'type' => 'best_fields',
                    'fields' => ['name^3', 'artist^2', 'venue_name', 'description'],
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 1,
                ],
            ]];

        $filter = [];
        if ($criteria->startDate !== null || $criteria->endDate !== null) {
            $range = [];
            if ($criteria->startDate !== null) {
                $range['gte'] = $criteria->startDate->format(DATE_ATOM);
            }
            if ($criteria->endDate !== null) {
                $range['lte'] = $criteria->endDate->format(DATE_ATOM);
            }
            $filter[] = ['range' => ['date' => $range]];
        }

        $body = [
            'from' => $criteria->offset(),
            'size' => $criteria->size,
            'track_total_hits' => true,
            'query' => ['bool' => ['must' => $must, 'filter' => $filter]],
        ];

        if ($keyword === '') {
            $body['sort'] = [['date' => 'asc']];
        }

        $response = $this->client->search([
            'index' => $this->index,
            'body' => $body,
        ])->asArray();

        return $this->mapResponse($response);
    }

    public function autocomplete(string $prefix, int $size): SearchResults
    {
        $response = $this->client->search([
            'index' => $this->index,
            'body' => [
                'size' => $size,
                '_source' => ['id', 'name', 'artist', 'venue_name', 'venue_city', 'date', 'min_price'],
                'query' => [
                    'match' => [
                        'name.autocomplete' => ['query' => $prefix, 'operator' => 'and'],
                    ],
                ],
            ],
        ])->asArray();

        return $this->mapResponse($response);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function mapResponse(array $response): SearchResults
    {
        $hits = array_map(
            static function (array $hit): EventHit {
                $source = $hit['_source'];

                return new EventHit(
                    id: (string) $source['id'],
                    name: (string) $source['name'],
                    artist: $source['artist'] ?? null,
                    venueName: $source['venue_name'] ?? null,
                    venueCity: $source['venue_city'] ?? null,
                    date: $source['date'] ?? null,
                    minPrice: isset($source['min_price']) ? (float) $source['min_price'] : null,
                    score: (float) ($hit['_score'] ?? 0.0),
                );
            },
            $response['hits']['hits'] ?? [],
        );

        return new SearchResults(
            hits: array_values($hits),
            total: (int) ($response['hits']['total']['value'] ?? 0),
            tookMs: (int) ($response['took'] ?? 0),
        );
    }
}
