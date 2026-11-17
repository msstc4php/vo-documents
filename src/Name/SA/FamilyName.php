<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\SA;

use Override;

/**
 * The family name in Arabic (`آل سعود`, `القحطاني`); `آل` may be a separate
 * word.
 * See {@see AbstractArabicNamePart} and docs/sources/SA.md "Names".
 */
final class FamilyName extends AbstractArabicNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Saudi Arabian family name';
    }
}
