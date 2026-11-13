<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IT;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name and given name(s). The derived Latin form is FAMILY<<GIVEN through the ICAO multinational Latin table
 * (`Nicolò` → `NICOLO`, `D'Angelo` → `DANGELO`, `De Luca` → `DE<LUCA`). Override policy {@see TransliterationVariant}:
 * no Italian rule lets a passport keep another Latin spelling, so an override must be another ICAO spelling of both
 * parts (`MULLER<<ANNA` for `Müller Anna`); an MRZ-style `<` for a hyphen is stored as the hyphen, and an override
 * equal to the derived form is not kept. See docs/sources/IT.md "Names".
 *
 * @see https://www.gazzettaufficiale.it/eli/gu/2010/05/19/115/sg/html DM 23 marzo 2010, n. 303/13 (GU n. 115 del
 *      19-5-2010), art. 1 (page 2 read "secondo la normativa ICAO")
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 *
 * @extends AbstractFamilyGivenPersonName<FamilyName, GivenName>
 */
final readonly class PersonName extends AbstractFamilyGivenPersonName
{
    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long or $latin is
     *                                    not a transliteration of the parts
     */
    public function __construct(FamilyName $family, GivenName $given, ?IcaoPersonName $latin = null)
    {
        parent::__construct($family, $given, $latin);
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): TransliterationVariant
    {
        return $builder->transliterationVariant(
            'Invalid Italian person name (the Latin form is not a transliteration of the name).',
        );
    }
}
