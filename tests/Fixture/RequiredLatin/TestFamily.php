<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

final class TestFamily extends AbstractNamePart
{
    #[Override]
    protected function letters(): string
    {
        return '\p{sc=Latin}';
    }

    #[Override]
    protected function label(): string
    {
        return 'test family name';
    }
}
