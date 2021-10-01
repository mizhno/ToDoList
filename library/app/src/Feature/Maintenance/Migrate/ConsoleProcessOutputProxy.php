<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate;

use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class ConsoleProcessOutputProxy
{
    /**
     * @var array<array-key,string>
     */
    private array $messages = [];

    public function __invoke(Process $process, StyleInterface $style): int
    {
        $result = $process->run(function(string $_type, string $buffer): void {
            $this->messages[] = trim($buffer);
        });

        $messages = implode(PHP_EOL, $this->messages);

        if ($style instanceof SymfonyStyle) {
            $style->block(messages: $messages, prefix: "\t→ ");
        } else {
            $style->text($messages);
        }

        return $result;
    }
}
