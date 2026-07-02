<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Domain\Search\SearchCriteria;
use App\Domain\Search\SearchEventsPort;
use App\Domain\Search\SearchResults;

/**
 * Records the last call so tests can assert exactly what the transport layer
 * hands to the domain port, without a running Elasticsearch.
 */
final class StubSearchEventsPort implements SearchEventsPort
{
    public ?SearchCriteria $lastCriteria = null;
    public ?string $lastPrefix = null;
    public ?int $lastSize = null;

    public function __construct(private readonly SearchResults $results)
    {
    }

    public function search(SearchCriteria $criteria): SearchResults
    {
        $this->lastCriteria = $criteria;

        return $this->results;
    }

    public function autocomplete(string $prefix, int $size): SearchResults
    {
        $this->lastPrefix = $prefix;
        $this->lastSize = $size;

        return $this->results;
    }
}
