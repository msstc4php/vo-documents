<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\NL;

use Override;

/**
 * One or several given names separated by spaces (`Jan Pieter`, `Hendrik-Jan`). See {@see AbstractDutchNamePart}
 * and docs/sources/NL.md "Names".
 */
final class GivenName extends AbstractDutchNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Dutch given name';
    }
}
