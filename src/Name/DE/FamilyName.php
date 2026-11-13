<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\DE;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Words of Latin letters (diacritics and `ß` included) joined by a hyphen (`Müller-Lüdenscheidt`), an apostrophe
 * (`D'Angelo`) or a space (`von der Leyen`). The documents write names in the character set String.Latin, of which
 * `\p{Latin}` is a superset; see docs/sources/DE.md "Names".
 *
 * @see https://www.gesetze-im-internet.de/pauswv/ PAuswV, Anlage 3 Abschnitt 1, Vorbemerkung Nr. 3 and 5 (character
 *      set String.Latin)
 * @see https://www.gesetze-im-internet.de/passv_2007/ PassV, Anlage 11, Vorbemerkung Nr. 3 and 5
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class FamilyName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'German family name';
    }
}
