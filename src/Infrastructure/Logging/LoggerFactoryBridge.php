<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Monolog\Level;
use Psr\Log\LoggerInterface;
use Ticketarget\Logging\LoggerFactory;

final class LoggerFactoryBridge
{
    public static function create(): LoggerInterface
    {
        return (new LoggerFactory(
            service: 'search-service',
            environment: (string) ($_SERVER['APP_ENV'] ?? 'production'),
            kafkaBrokers: (string) ($_SERVER['KAFKA_BROKERS'] ?? ''),
            kafkaTopic: (string) ($_SERVER['KAFKA_LOG_TOPIC'] ?? 'logs.app'),
            level: Level::Debug,
        ))->create('search-service');
    }
}
