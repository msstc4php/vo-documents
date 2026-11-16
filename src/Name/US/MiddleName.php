<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\US;

use Override;

/**
 * Middle name or names (`Michael`, `Lee Ann`), the second secondary identifier after {@see GivenName}. Letters and
 * separators as {@see AbstractAmericanNamePart}; see docs/sources/US.md "Names".
 *
 * @see https://fam.state.gov/fam/08fam/08fam040301.html 8 FAM 403.1-3
 */
final class MiddleName extends AbstractAmericanNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'American middle name';
    }
}
