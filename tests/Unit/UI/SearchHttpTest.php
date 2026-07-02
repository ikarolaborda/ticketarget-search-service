<?php

declare(strict_types=1);

namespace App\Tests\Unit\UI;

use App\Application\Search\SearchEventsHandler;
use App\Domain\Search\SearchResults;
use App\Tests\Support\StubSearchEventsPort;
use App\UI\Http\AutocompleteController;
use App\UI\Http\SearchController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SearchHttpTest extends TestCase
{
    private StubSearchEventsPort $port;
    private SearchEventsHandler $handler;

    protected function setUp(): void
    {
        $this->port = new StubSearchEventsPort(new SearchResults([], 0, 1));
        $this->handler = new SearchEventsHandler($this->port);
    }

    public function test_search_treats_an_empty_keyword_as_browse_mode(): void
    {
        $response = (new SearchController($this->handler))(Request::create('/search'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($this->port->lastCriteria);
        $this->assertSame('', $this->port->lastCriteria->keyword);
        $this->assertSame(1, $this->port->lastCriteria->page);
        $this->assertSame(20, $this->port->lastCriteria->size);
    }

    public function test_search_passes_keyword_and_pagination_to_the_port(): void
    {
        (new SearchController($this->handler))(
            Request::create('/search', 'GET', ['keyword' => '  bruno ', 'page' => '3', 'size' => '12']),
        );

        $this->assertSame('bruno', $this->port->lastCriteria->keyword);
        $this->assertSame(3, $this->port->lastCriteria->page);
        $this->assertSame(12, $this->port->lastCriteria->size);
        $this->assertSame(24, $this->port->lastCriteria->offset());
    }

    public function test_search_clamps_page_and_size_to_sane_bounds(): void
    {
        (new SearchController($this->handler))(
            Request::create('/search', 'GET', ['page' => '0', 'size' => '5000']),
        );

        $this->assertSame(1, $this->port->lastCriteria->page);
        $this->assertSame(100, $this->port->lastCriteria->size);
    }

    public function test_search_parses_date_filters(): void
    {
        (new SearchController($this->handler))(
            Request::create('/search', 'GET', ['startDate' => '2026-08-01', 'endDate' => 'not-a-date']),
        );

        $this->assertSame('2026-08-01', $this->port->lastCriteria->startDate?->format('Y-m-d'));
        $this->assertNull($this->port->lastCriteria->endDate);
    }

    public function test_autocomplete_short_circuits_on_an_empty_prefix(): void
    {
        $response = (new AutocompleteController($this->handler))(
            Request::create('/search/autocomplete', 'GET', ['q' => '   ']),
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"total":0,"took_ms":0,"results":[]}', $response->getContent());
        $this->assertNull($this->port->lastPrefix, 'The engine must not be queried for an empty prefix');
    }

    public function test_autocomplete_trims_the_prefix_and_clamps_the_size(): void
    {
        (new AutocompleteController($this->handler))(
            Request::create('/search/autocomplete', 'GET', ['q' => ' br ', 'size' => '50']),
        );

        $this->assertSame('br', $this->port->lastPrefix);
        $this->assertSame(10, $this->port->lastSize);
    }
}
