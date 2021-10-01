<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate\Console;

use InvalidArgumentException;
use Library\Feature\Maintenance\Migrate\Console\Input\MigrationOffsetExtractor;
use Library\Feature\Maintenance\Migrate\ConsoleProcessOutputProxy;
use Library\Feature\Maintenance\Migrate\MigrateProcessBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MigrateUpCommand extends Command
{
    public function __construct(
        private MigrateProcessBuilder $migrateProcessBuilder,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('migrate:up')
            ->setDescription('Apply number of migrations')
            ->addArgument('offset', InputArgument::OPTIONAL, 'Number of versions to apply');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug('Test');

        $style = new SymfonyStyle($input, $output);

        try {
            $offset = (new MigrationOffsetExtractor())($input);
        } catch (InvalidArgumentException $e) {
            $style->error("Invalid offset provided: {$e->getMessage()}.");

            return Command::FAILURE;
        }

        $number = $offset ?: 'all';
        $process = $this->migrateProcessBuilder->up($offset);

        $style->info("Applying {$number} migration(s)");

        return (new ConsoleProcessOutputProxy())($process, $style);
    }
}
