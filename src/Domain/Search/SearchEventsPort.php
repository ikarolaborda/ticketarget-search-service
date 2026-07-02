<?php

declare(strict_types=1);

namespace App\Domain\Search;

/**
 * Outbound port for searching the event catalog. The domain knows only this
 * contract; the search engine (Elasticsearch today) is an implementation detail.
 */
interface SearchEventsPort
{
    public function search(SearchCriteria $criteria): SearchResults;

    /**
     * Prefix/as-you-type suggestions over event names, backed by an edge-ngram
     * analyzer for sub-100ms typeahead.
     */
    public function autocomplete(string $prefix, int $size): SearchResults;
}
