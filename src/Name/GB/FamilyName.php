<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\GB;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Surname as the birth certificate or change-of-name document writes it, letter case kept: words of Latin letters
 * (diacritics included, `Llŷr`) joined by a hyphen (`Smith-Jones`), an apostrophe (`O'Brien`) or a space
 * (`ap Rhys`). No UK source publishes a letter list for the name itself; see docs/sources/GB.md "Names".
 *
 * @see https://www.gov.uk/government/publications/names-names-that-cannot-be-used-in-passports/names-names-that-cannot-be-used-in-passports-accessible
 *      HM Passport Office, Names: names that cannot be used in passports, v19.0, § 2.2
 */
final class FamilyName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'British family name';
    }
}
