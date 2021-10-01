<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate\Console;

use InvalidArgumentException;
use Library\Feature\Maintenance\Migrate\Console\Input\MigrationOffsetExtractor;
use Library\Feature\Maintenance\Migrate\ConsoleProcessOutputProxy;
use Library\Feature\Maintenance\Migrate\MigrateProcessBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MigrateDownCommand extends Command
{
    public function __construct(private MigrateProcessBuilder $migrateProcessBuilder)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('migrate:down')
            ->setDescription('Rollback number of migrations')
            ->addArgument('offset', InputArgument::OPTIONAL, 'Number of versions to rollback');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        try {
            $offset = (new MigrationOffsetExtractor())($input);
        } catch (InvalidArgumentException $e) {
            $style->error("Invalid offset provided: {$e->getMessage()}.");

            return Command::FAILURE;
        }

        $number = $offset ?: 'all';
        $process = $this->migrateProcessBuilder->down($offset);

        $style->info("Rollback {$number} migration(s)");

        return (new ConsoleProcessOutputProxy())($process, $style);
    }
}
