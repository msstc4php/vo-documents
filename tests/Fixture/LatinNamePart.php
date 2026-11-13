<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * Separators that are regex metacharacters, to prove AbstractNamePart quotes them.
 */
final class LatinNamePart extends AbstractNamePart
{
    #[Override]
    protected function letters(): string
    {
        return 'a-z';
    }

    #[Override]
    protected function label(): string
    {
        return 'test name';
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return '/.]';
    }
}
