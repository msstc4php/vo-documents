<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CA;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Surname as the proof of citizenship writes it, letter case kept: words of Latin letters, English and French
 * diacritics included (`Gagné`, `Bérubé-Lévesque`), joined by a hyphen, an apostrophe (`O'Neill`, `D'Amours`) or a
 * space (`de la Fontaine`). The Passport Program prints "the Roman alphabet and some accented letters" without
 * listing them, so no closed letter set is applied; see docs/sources/CA.md "Names".
 *
 * @see https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-passports/change-name.html IRCC,
 *      "Changing the name on your passport or other travel document"
 */
final class FamilyName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Canadian family name';
    }
}
