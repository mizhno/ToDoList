<?php

declare(strict_types=1);

namespace Library\Feature\Maintenance\Migrate\Console\Input;

use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Assert\Assert;

final class MigrationOffsetExtractor
{
    public function __invoke(InputInterface $input): ?int
    {
        /** @var null|string $offset */
        $offset = $input->getArgument('offset');

        if ($offset === null) {
            return $offset;
        }

        Assert::integerish($offset, 'migration offset must be an integer');

        $offset = (int) $offset;

        Assert::positiveInteger($offset, 'migration offset must be positive number');
        Assert::greaterThan($offset, 0, 'migration offset must be greater than zero');

        return $offset;
    }
}
