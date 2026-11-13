<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

final class SingleWordNamePart extends AbstractNamePart
{
    #[Override]
    protected function letters(): string
    {
        return 'a-z';
    }

    #[Override]
    protected function label(): string
    {
        return 'single-word name';
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return '';
    }
}
