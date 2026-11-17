<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\MX;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * A Mexican name part as RENAPO captures it, "tal cual viene en el documento probatorio" (Reglas 2021, 5.1, 1.4): Latin
 * letters, words joined by a hyphen, an apostrophe, a space or a slash (`D/Amico`), and a full stop after or between
 * letters (`Ma.`, `J.`, `M.A.`). The ICAO form omits the slash and the full stop (Doc 9303 Part 3 §4.6, "Other
 * punctuation characters"; {@see PersonName}). See docs/sources/MX.md "Names".
 */
abstract class AbstractMexicanNamePart extends AbstractLatinNamePart
{
    /** Allowed in a part, omitted from the ICAO form. */
    public const array OMITTED_PUNCTUATION = ['.', '/'];

    #[Override]
    protected function innerSeparators(): string
    {
        return "-' /";
    }

    #[Override]
    protected function word(): string
    {
        return $this->wordWithAbbreviationDots();
    }
}
