<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use Override;

/**
 * The reading of the family name (氏の振り仮名), e.g. `サトウ`. See {@see AbstractJapaneseReading} and docs/sources/JP.md.
 */
final class FamilyNameReading extends AbstractJapaneseReading
{
    #[Override]
    protected function label(): string
    {
        return 'Japanese family name reading';
    }
}
