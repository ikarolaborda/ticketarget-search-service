<?php

declare(strict_types=1);

namespace App\UI\Http;

use App\Application\Search\SearchEventsHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class AutocompleteController
{
    public function __construct(private SearchEventsHandler $handler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $prefix = trim((string) $request->query->get('q', ''));

        if ($prefix === '') {
            return new JsonResponse(['total' => 0, 'took_ms' => 0, 'results' => []]);
        }

        $size = min(10, max(1, $request->query->getInt('size', 8)));

        return new JsonResponse($this->handler->autocomplete($prefix, $size)->toArray());
    }
}
