<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CA;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use Override;

/**
 * Surname and optional given names: a person without a family name has the name in the surname field and no given
 * name (IRCC: "enter all your given name(s) in the surname field and leave the given name field blank"), here `null`.
 * The derived Latin form is SURNAME<<GIVEN, or SURNAME alone, through the ICAO multinational Latin table
 * (`Bérubé-Lévesque Jean-François` → `BERUBE-LEVESQUE<<JEAN-FRANCOIS`). Override policy {@see AnyIcaoSpelling}: the
 * Passport Program prints only "the Roman alphabet and some accented letters" and points to "alternative spellings of
 * names", and lets an adult "drop given names" or "invert given names" against the proof of citizenship, so a
 * passport name need not be a transliteration: any valid ICAO name is kept as the override, as typed, also when a
 * letter has no ICAO spelling; one equal to the derived form is not kept. See docs/sources/CA.md "Names".
 *
 * @see https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-passports/change-name.html IRCC,
 *      "Changing the name on your passport or other travel document"
 * @see https://ircc.canada.ca/english/helpcentre/answer.asp?qnum=014 IRCC Help Centre, "What do you mean by surname?"
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 *
 * @extends AbstractFamilyGivenPersonName<FamilyName, GivenName|null>
 */
final readonly class PersonName extends AbstractFamilyGivenPersonName
{
    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling and no override is given, or the Latin
     *                                    form is too long
     */
    public function __construct(FamilyName $family, ?GivenName $given, ?IcaoPersonName $latin = null)
    {
        parent::__construct($family, $given, $latin);
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): AnyIcaoSpelling
    {
        return new AnyIcaoSpelling();
    }
}
