<?php

declare(strict_types=1);

namespace Library\Infrastructure\Config;

use RuntimeException;

final class MissedValueException extends RuntimeException
{
    public function __construct(string $variable)
    {
        parent::__construct("Unable to locate configuration value for '{$variable}'");
    }
}
