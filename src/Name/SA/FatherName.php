<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\SA;

use Override;

/**
 * The father's name in Arabic (`عبد الله`).
 * See {@see AbstractArabicNamePart} and docs/sources/SA.md "Names".
 */
final class FatherName extends AbstractArabicNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Saudi Arabian father name';
    }
}
