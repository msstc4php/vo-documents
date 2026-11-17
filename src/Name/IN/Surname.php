<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * The surname in Latin letters, of one or two words (`Jain`, `Roy Choudhary`, `Das Gupta`: "Surname could have two
 * words"). See docs/sources/IN.md "Names".
 *
 * @see https://www.passportindia.gov.in/AppOnlineProject/pdf/ApplicationformInstructionBooklet-V3.0.pdf Column 2.1
 */
final class Surname extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Indian surname';
    }
}
