<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate\Console;

use InvalidArgumentException;
use Library\Feature\Maintenance\Migrate\Console\Input\MigrationNameExtractor;
use Library\Feature\Maintenance\Migrate\ConsoleProcessOutputProxy;
use Library\Feature\Maintenance\Migrate\MigrateProcessBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CreateMigrationCommand extends Command
{
    public function __construct(
        private MigrateProcessBuilder $migrateProcessBuilder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('migrate:create')
            ->setDescription('Create new migration')
            ->addArgument('name', InputArgument::REQUIRED, 'Migration name (a-Z, _, -)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        try {
            $name = (new MigrationNameExtractor())($input);
        } catch (InvalidArgumentException $e) {
            $style->error("Invalid name provided: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $process = $this->migrateProcessBuilder->create($name);

        $style->info("Creating {$name} migration…");

        return (new ConsoleProcessOutputProxy())($process, $style);
    }
}
