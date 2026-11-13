<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IT;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Words of Latin letters (diacritics included: `Nicolò`, `Mazzà`) joined by a hyphen, an apostrophe (`D'Angelo`)
 * or a space (`De Luca`, `Lo Russo`). No reachable Italian text lists the letters of a civil-status name, so the
 * package's Latin default applies; see docs/sources/IT.md "Names".
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class FamilyName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Italian family name';
    }
}
