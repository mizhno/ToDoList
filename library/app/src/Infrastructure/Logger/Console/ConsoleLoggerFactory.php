<?php

declare(strict_types=1);

namespace Library\Infrastructure\Logger\Console;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class ConsoleLoggerFactory
{
    public function __invoke(): Logger
    {
        $fileHandler = new StreamHandler('php://stdout', Logger::DEBUG);

        $fileHandler->setFormatter(new JsonFormatter());

        $logger = new Logger('Console');

        $logger->pushHandler($fileHandler);

        return $logger;
    }
}
