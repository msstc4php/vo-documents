<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CN;

use Override;

/**
 * The family name in Han characters (`张`, `欧阳`); a minority name that has no family/given split is given whole to
 * one of the two parts. See {@see AbstractChineseNamePart} and docs/sources/CN.md "Names".
 */
final class FamilyName extends AbstractChineseNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Chinese family name';
    }
}
