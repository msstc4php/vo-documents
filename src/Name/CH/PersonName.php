<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CH;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Amtlicher Name and optional given names: a person without a given name has `***` in the document's given-name field
 * (SR 143.111 Art. 5 Abs. 4), here `null`. The derived Latin form is FAMILY<<GIVEN, or FAMILY without given names,
 * through the ICAO multinational Latin table (`Müller` → `MUELLER`, `Schweizer Sample` → `SCHWEIZER<SAMPLE`). Override
 * policy {@see TransliterationVariant}: no Swiss rule lets a passport keep another Latin spelling, so an override must
 * be another ICAO spelling of the parts; an MRZ-style `<` for a hyphen is stored as the hyphen, and an override equal
 * to the derived form is not kept. See docs/sources/CH.md "Names".
 *
 * @see https://www.fedlex.admin.ch/eli/cc/2010/96/de SR 143.111, Art. 4 and 5
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
            'Invalid Swiss person name (the Latin form is not a transliteration of the name).',
        );
    }
}
