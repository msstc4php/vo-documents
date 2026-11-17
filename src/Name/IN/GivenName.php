<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * The given name in Latin letters (`Piyush Kumar`); several names are one part, and a person who uses no surname writes
 * the whole name here, as the Passport Seva form asks. See docs/sources/IN.md "Names".
 *
 * @see https://www.passportindia.gov.in/AppOnlineProject/pdf/ApplicationformInstructionBooklet-V3.0.pdf Column 2.1
 */
final class GivenName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Indian given name';
    }
}
