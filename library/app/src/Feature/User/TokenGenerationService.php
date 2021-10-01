<?php

declare(strict_types=1);

namespace Library\Feature\User;

final class TokenGenerationService
{
    public function generate(): string
    {
        return bin2hex(random_bytes(16));
    }
}
