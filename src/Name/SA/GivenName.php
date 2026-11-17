<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\SA;

use Override;

/**
 * The given name in Arabic (`محمد`).
 * See {@see AbstractArabicNamePart} and docs/sources/SA.md "Names".
 */
final class GivenName extends AbstractArabicNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Saudi Arabian given name';
    }
}
