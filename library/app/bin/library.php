<?php

declare(strict_types=1);

namespace Library;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DI\ContainerBuilder;
use Library\Feature\Maintenance\Migrate\Console\CreateMigrationCommand;
use Library\Feature\Maintenance\Migrate\Console\MigrateDownCommand;
use Library\Feature\Maintenance\Migrate\Console\MigrateUpCommand;
use Library\Feature\Maintenance\Migrate\Console\MigrationSeedCommand;
use Library\Feature\Maintenance\Migrate\MigrateProcessBuilder;
use Library\Feature\Maintenance\Migrate\MigrateProcessBuilderFactory;
use Library\Infrastructure\Config\MissedValueException;
use Library\Infrastructure\Database\Connection;
use Library\Infrastructure\Database\ConnectionFactory;
use Library\Infrastructure\Logger\Console\ConsoleLoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use function DI\autowire;
use function DI\factory;

$container = (static function(): ContainerInterface {
    return (new ContainerBuilder())
        ->addDefinitions([
            // Config
            'version' => getenv('LIBRARY_CONSOLE_VERSION') ?: throw new MissedValueException('version'),
            'migration' => [
                'dsn' => sprintf(
                    'postgres://%s:%s@%s:%d/%s?sslmode=disable',
                    getenv('DATABASE_USER') ?: throw new MissedValueException('migration.dsn.user'),
                    getenv('DATABASE_PASSWORD') ?: throw new MissedValueException('migration.dsn.password'),
                    getenv('DATABASE_HOST') ?: throw new MissedValueException('migration.dsn.host'),
                    getenv('DATABASE_PORT') ?: throw new MissedValueException('migration.dsn.port'),
                    getenv('DATABASE_DB') ?: throw new MissedValueException('migration.dsn.db'),
                ),
                'path' => dirname(__DIR__) . '/migrations',
            ],
            'database' => [
                'dsn' => sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    getenv('DATABASE_HOST') ?: throw new MissedValueException('dsn.host'),
                    getenv('DATABASE_PORT') ?: throw new MissedValueException('dsn.port'),
                    getenv('DATABASE_DB') ?: throw new MissedValueException('dsn.db'),
                ),
                'user' => getenv('DATABASE_USER') ?: throw new MissedValueException('database.user'),
                'password' => getenv('DATABASE_PASSWORD') ?: throw new MissedValueException('database.password'),
            ],
            // Command list
            'commands' => [
                'migrate:up' => MigrateUpCommand::class,
                'migrate:down' => MigrateDownCommand::class,
                'migrate:create' => CreateMigrationCommand::class,
                'migrate:seed' => MigrationSeedCommand::class,
            ],
            // Commands
            CreateMigrationCommand::class => autowire(),
            MigrateUpCommand::class => autowire(),
            MigrateDownCommand::class => autowire(),
            MigrationSeedCommand::class => autowire(),
            // Infrastructure
            Connection::class => factory(ConnectionFactory::class),
            LoggerInterface::class => factory(ConsoleLoggerFactory::class),
            MigrateProcessBuilder::class => factory(MigrateProcessBuilderFactory::class),
        ])
        ->useAutowiring(true)
        ->useAnnotations(false)
        ->enableCompilation('/tmp/.php-di-console')
        ->build();
})();

/** @var string $version */
$version = $container->get('version');
/** @var array<string,class-string> $commands */
$commands = $container->get('commands');

$app = new Application('Library', $version);

$app->setCatchExceptions(false);
$app->setCommandLoader(new ContainerCommandLoader($container, $commands));

try {
    $app->run();
} catch (Throwable $exception) {
    $output = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());

    $output->title('An error has occurred');
    $output->section('Exception type: ' . $exception::class);
    $output->table(
        ['Exception thrown message: ', $exception->getMessage()],
        [
            ['File: ', $exception->getFile()],
            ['Error code', $exception->getCode()],
            ['Error line', $exception->getLine()],
        ]
    );
}
