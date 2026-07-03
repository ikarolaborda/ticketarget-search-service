<?php

declare(strict_types=1);

namespace App\Domain\Search;

final readonly class EventHit
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $artist,
        public ?string $venueName,
        public ?string $venueCity,
        public ?string $date,
        public ?float $minPrice,
        public float $score,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'artist' => $this->artist,
            'venue_name' => $this->venueName,
            'venue_city' => $this->venueCity,
            'date' => $this->date,
            'min_price' => $this->minPrice,
            'score' => $this->score,
        ];
    }
}
