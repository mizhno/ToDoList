<?php

declare(strict_types=1);

namespace Library\Infrastructure\Database;

use PDO;
use Psr\Container\ContainerInterface;

final class ConnectionFactory
{
    public function __invoke(ContainerInterface $container): Connection
    {
        /** @var array<string,string> $database */
        $database = $container->get('database');

        return new Connection(
            new PDO(
                $database['dsn'],
                $database['user'],
                $database['password'],
            ),
        );
    }
}
