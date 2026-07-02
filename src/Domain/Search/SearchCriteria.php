<?php

declare(strict_types=1);

namespace App\Domain\Search;

use DateTimeImmutable;

final readonly class SearchCriteria
{
    public function __construct(
        public string $keyword,
        public ?DateTimeImmutable $startDate = null,
        public ?DateTimeImmutable $endDate = null,
        public int $page = 1,
        public int $size = 20,
    ) {
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->size;
    }
}
