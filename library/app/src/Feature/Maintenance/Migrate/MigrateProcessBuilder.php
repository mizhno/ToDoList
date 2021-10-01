<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate;

use Symfony\Component\Process\Process;

final class MigrateProcessBuilder
{
    public function __construct(private string $dsn, private string $path)
    {
    }

    public function up(?int $offset): Process
    {
        return new Process(
            array_filter([
                'migrate',
                '-path', $this->path,
                '-database', $this->dsn,
                'up', $offset,
            ]),
        );
    }

    public function down(?int $offset): Process
    {
        return (new Process(
            array_filter([
                'migrate',
                '-path', $this->path,
                '-database', $this->dsn,
                'down', $offset,
            ]),
        ))->setInput('y');
    }

    public function create(string $name): Process
    {
        return new Process(
            [
                'migrate',
                'create',
                '-ext', 'sql',
                '-dir', $this->path,
                $name,
            ],
        );
    }
}
