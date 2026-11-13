<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\NL;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name (prefixes included) and optional given names: a person known by one name only has it as the family name
 * (Paspoortuitvoeringsregeling art. 23 lid 4). The derived Latin form is FAMILY<<GIVEN, or FAMILY for one name, through
 * the ICAO multinational Latin table (`van der Berg` → `VAN<DER<BERG`, `'t Hart` → `T<HART`, `Ëlla` → `ELLA`). Override
 * policy {@see TransliterationVariant}: no Dutch rule lets a passport keep another Latin spelling, so an override must
 * be another ICAO spelling of the parts; an MRZ-style `<` for a hyphen is stored as the hyphen, and an override equal
 * to the derived form is not kept. See docs/sources/NL.md "Names".
 *
 * @see https://wetten.overheid.nl/BWBR0012811/ Paspoortuitvoeringsregeling Nederland 2001, art. 23 lid 3 and 4
 * @see https://wetten.overheid.nl/BWBR0005212/ Paspoortwet, art. 3 lid 1
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 *
 * @extends AbstractFamilyGivenPersonName<FamilyName, GivenName|null>
 */
final readonly class PersonName extends AbstractFamilyGivenPersonName
{
    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long or $latin is
     *                                    not a transliteration of the parts
     */
    public function __construct(FamilyName $family, ?GivenName $given, ?IcaoPersonName $latin = null)
    {
        parent::__construct($family, $given, $latin);
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): TransliterationVariant
    {
        return $builder->transliterationVariant(
            'Invalid Dutch person name (the Latin form is not a transliteration of the name).',
        );
    }
}
