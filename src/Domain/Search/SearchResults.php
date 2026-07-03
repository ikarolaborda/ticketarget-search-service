<?php

declare(strict_types=1);

namespace App\Domain\Search;

final readonly class SearchResults
{
    /**
     * @param list<EventHit> $hits
     */
    public function __construct(
        public array $hits,
        public int $total,
        public int $tookMs,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'took_ms' => $this->tookMs,
            'results' => array_map(static fn (EventHit $hit): array => $hit->toArray(), $this->hits),
        ];
    }
}
