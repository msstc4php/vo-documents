<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use Override;

/**
 * The reading of the given name (名の振り仮名), e.g. `たろう`. See {@see AbstractJapaneseReading} and docs/sources/JP.md.
 */
final class GivenNameReading extends AbstractJapaneseReading
{
    #[Override]
    protected function label(): string
    {
        return 'Japanese given name reading';
    }
}
