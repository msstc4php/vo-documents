<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\DE;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name and given name(s). The derived Latin form is FAMILY<<GIVEN through the ICAO multinational Latin table
 * (`Müller` → `MUELLER`, `Groß` → `GROSS`). Override policy {@see TransliterationVariant}: no German rule lets a
 * passport keep another Latin spelling, so an override must be another ICAO spelling of both parts (`MULLER<<JURGEN`
 * for `Müller Jürgen`); an MRZ-style `<` for a hyphen is stored as the hyphen, and an override equal to the derived
 * form is not kept. See docs/sources/DE.md "Names".
 *
 * @see https://www.gesetze-im-internet.de/pa_g_1986/__4.html PassG § 4 Abs. 2 (machine-readable zone)
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
            'Invalid German person name (the Latin form is not a transliteration of the name).',
        );
    }
}
