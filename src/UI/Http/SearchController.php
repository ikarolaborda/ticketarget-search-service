<?php

declare(strict_types=1);

namespace App\UI\Http;

use App\Application\Search\SearchEventsHandler;
use App\Domain\Search\SearchCriteria;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class SearchController
{
    public function __construct(private SearchEventsHandler $handler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        // An empty keyword is allowed: it lists the whole catalog (browse mode),
        // which the landing page paginates.
        $criteria = new SearchCriteria(
            keyword: trim((string) $request->query->get('keyword', '')),
            startDate: $this->parseDate($request->query->get('startDate')),
            endDate: $this->parseDate($request->query->get('endDate')),
            page: max(1, $request->query->getInt('page', 1)),
            size: min(100, max(1, $request->query->getInt('size', 20))),
        );

        return new JsonResponse($this->handler->handle($criteria)->toArray());
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(DATE_ATOM, $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date ?: null;
    }
}
