<?php

declare(strict_types=1);

namespace App\Application\Search;

use App\Domain\Search\SearchCriteria;
use App\Domain\Search\SearchEventsPort;
use App\Domain\Search\SearchResults;

/**
 * Application service orchestrating the search use case. Kept free of transport
 * and engine concerns so it can be reused by HTTP, CLI, or message handlers.
 */
final readonly class SearchEventsHandler
{
    public function __construct(private SearchEventsPort $search)
    {
    }

    public function handle(SearchCriteria $criteria): SearchResults
    {
        return $this->search->search($criteria);
    }

    public function autocomplete(string $prefix, int $size): SearchResults
    {
        return $this->search->autocomplete($prefix, $size);
    }
}
