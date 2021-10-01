<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate;

use Psr\Container\ContainerInterface;

final class MigrateProcessBuilderFactory
{
    public function __invoke(ContainerInterface $container): MigrateProcessBuilder
    {
        /** @var array<string,string> $migration */
        $migration = $container->get('migration');

        return new MigrateProcessBuilder(
            $migration['dsn'],
            $migration['path'],
        );
    }
}
