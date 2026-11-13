<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\FR;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name and given name(s). The derived Latin form is FAMILY<<GIVEN through the ICAO multinational Latin table
 * (`Cœur` → `COEUR`, `Lætitia` → `LAETITIA`, `Hélène` → `HELENE`, `D'Artagnan` → `DARTAGNAN`). Override policy {@see
 * TransliterationVariant}: no French text lets a passport keep another Latin spelling, so an override must be another
 * ICAO spelling of both parts (`MULLER<<JOEL` for `Müller Joël`); an MRZ-style `<` for a hyphen is stored as the
 * hyphen, and an override equal to the derived form is not kept. See docs/sources/FR.md "Names".
 *
 * @see https://www.legifrance.gouv.fr/circulaire/id/38565 Circulaire du 23 juillet 2014 relative à l'état civil, § 1
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
            'Invalid French person name (the Latin form is not a transliteration of the name).',
        );
    }
}
