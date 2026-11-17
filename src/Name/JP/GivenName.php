<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use Override;

/**
 * The given name (名), e.g. `太郎`, `ヨシ子`. See {@see AbstractJapaneseNamePart} and docs/sources/JP.md.
 */
final class GivenName extends AbstractJapaneseNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Japanese given name';
    }
}
