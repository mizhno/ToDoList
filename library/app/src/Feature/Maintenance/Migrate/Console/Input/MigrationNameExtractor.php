<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate\Console\Input;

use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Assert\Assert;

final class MigrationNameExtractor
{
    public function __invoke(InputInterface $input): string
    {
        /** @var null|string $name */
        $name = $input->getArgument('name');

        Assert::string($name, 'migration name must be of string type, %s given');
        Assert::notEmpty($name, 'migration name must not be empty');
        Assert::regex($name, '#^[a-z_-]+$#i', "migration name must contain only latin and/or '_', '-' symbols");

        return $name;
    }
}
