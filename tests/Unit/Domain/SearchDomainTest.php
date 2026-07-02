<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Search\EventHit;
use App\Domain\Search\SearchCriteria;
use App\Domain\Search\SearchResults;
use PHPUnit\Framework\TestCase;

final class SearchDomainTest extends TestCase
{
    public function test_criteria_computes_the_engine_offset_from_the_page(): void
    {
        $this->assertSame(0, (new SearchCriteria(keyword: ''))->offset());
        $this->assertSame(0, (new SearchCriteria(keyword: '', page: 1, size: 12))->offset());
        $this->assertSame(12, (new SearchCriteria(keyword: '', page: 2, size: 12))->offset());
        $this->assertSame(40, (new SearchCriteria(keyword: '', page: 3, size: 20))->offset());
    }

    public function test_results_serialize_to_the_public_api_shape(): void
    {
        $hit = new EventHit(
            id: '019f0000-0000-7000-8000-000000000001',
            name: 'Bruno Mars Live',
            artist: 'Bruno Mars',
            venueName: 'Big Arena',
            venueCity: 'Lisbon',
            date: '2026-09-27T14:56:36+00:00',
            minPrice: 70.12,
            score: 2.5,
        );

        $payload = (new SearchResults([$hit], 1, 28))->toArray();

        $this->assertSame(1, $payload['total']);
        $this->assertSame(28, $payload['took_ms']);
        $this->assertSame(
            [
                'id' => '019f0000-0000-7000-8000-000000000001',
                'name' => 'Bruno Mars Live',
                'artist' => 'Bruno Mars',
                'venue_name' => 'Big Arena',
                'venue_city' => 'Lisbon',
                'date' => '2026-09-27T14:56:36+00:00',
                'min_price' => 70.12,
                'score' => 2.5,
            ],
            $payload['results'][0],
        );
    }

    public function test_results_serialize_empty_hit_lists(): void
    {
        $this->assertSame(
            ['total' => 0, 'took_ms' => 3, 'results' => []],
            (new SearchResults([], 0, 3))->toArray(),
        );
    }
}
