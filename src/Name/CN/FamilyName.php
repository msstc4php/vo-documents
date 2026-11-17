<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CN;

use Override;

/**
 * The family name in Han characters (`张`, `欧阳`); a name with no family/given split (a minority-nationality name)
 * is stored whole here, with a null given name. See {@see AbstractChineseNamePart} and docs/sources/CN.md "Names".
 */
final class FamilyName extends AbstractChineseNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Chinese family name';
    }
}
