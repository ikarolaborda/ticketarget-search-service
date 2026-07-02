<?php

declare(strict_types=1);

namespace App\Infrastructure\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

final class ClientFactory
{
    public static function create(string $hosts): Client
    {
        return ClientBuilder::create()
            ->setHosts(array_map('trim', explode(',', $hosts)))
            ->setRetries(2)
            ->build();
    }
}
