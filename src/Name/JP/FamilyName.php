<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use Override;

/**
 * The family name (氏), e.g. `佐藤`. See {@see AbstractJapaneseNamePart} and docs/sources/JP.md.
 */
final class FamilyName extends AbstractJapaneseNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Japanese family name';
    }
}
