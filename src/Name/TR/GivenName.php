<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\TR;

use Override;

/**
 * Adı: one or several given names separated by spaces (`Ahmet Can`, `İnci`). See {@see AbstractTurkishNamePart} and
 * docs/sources/TR.md "Names".
 */
final class GivenName extends AbstractTurkishNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Turkish given name';
    }
}
