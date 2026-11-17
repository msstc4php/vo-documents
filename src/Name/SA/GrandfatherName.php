<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\SA;

use Override;

/**
 * The grandfather's name in Arabic (`سعد`).
 * See {@see AbstractArabicNamePart} and docs/sources/SA.md "Names".
 */
final class GrandfatherName extends AbstractArabicNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Saudi Arabian grandfather name';
    }
}
