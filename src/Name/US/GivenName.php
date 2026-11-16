<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\US;

use Override;

/**
 * First name, simple or compound (`Mary Ann`, `José`). Letters and separators as {@see AbstractAmericanNamePart}; see
 * docs/sources/US.md "Names".
 *
 * @see https://fam.state.gov/fam/08fam/08fam040301.html 8 FAM 403.1-3
 */
final class GivenName extends AbstractAmericanNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'American given name';
    }
}
