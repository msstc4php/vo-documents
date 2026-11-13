<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\FR;

use Override;

/**
 * See {@see AbstractFrenchNamePart} and docs/sources/FR.md "Names".
 */
final class FamilyName extends AbstractFrenchNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'French family name';
    }
}
