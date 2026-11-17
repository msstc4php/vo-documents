<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CN;

use Override;

/**
 * The given name in Han characters (`伟`, `小明`). See {@see AbstractChineseNamePart} and docs/sources/CN.md "Names".
 */
final class GivenName extends AbstractChineseNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Chinese given name';
    }
}
