<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\GB;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use Override;

/**
 * Surname and optional forenames: a person with one name gets `XXX` as the passport forename, which "does not form part
 * of their name", here `null`. The derived Latin form is SURNAME<<FORENAMES, or SURNAME alone, through the ICAO
 * multinational Latin table (`O'Brien Siân` → `OBRIEN<<SIAN`). Override policy {@see AnyIcaoSpelling}: HM Passport
 * Office writes a spelling shown by an official document or a foreign authority's letter when the table has none the
 * holder uses, so any valid ICAO name is kept as the override, as typed (an MRZ-style `<` for a hyphen stays `<`), also
 * when a letter has no ICAO spelling; one equal to the derived form is not kept, and `XXX` as the only forename of a
 * person with one name is dropped (`SMITH<<XXX` is `SMITH`). See docs/sources/GB.md "Names".
 *
 * @see https://www.gov.uk/government/publications/names-names-that-cannot-be-used-in-passports/names-names-that-cannot-be-used-in-passports-accessible
 *      HM Passport Office, Names: names that cannot be used in passports, v19.0, § 2.2
 * @see https://www.gov.uk/government/publications/how-to-show-uncommon-names-in-passports-caseworker-guidance/names-how-to-show-uncommon-names-in-passports
 *      HM Passport Office, Names: how to show uncommon names in passports, v18.0, "When the customer only has one name"
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6,
 *      §6.A
 *
 * @extends AbstractFamilyGivenPersonName<FamilyName, GivenName|null>
 */
final readonly class PersonName extends AbstractFamilyGivenPersonName
{
    /** The passport forename of a person with one name, "not part of their name" (GB.md (4)). */
    private const string NO_FORENAME = 'XXX';

    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling and no override is given, or the Latin
     *                                    form is too long
     */
    public function __construct(FamilyName $family, ?GivenName $given, ?IcaoPersonName $latin = null)
    {
        $mononymOverride = !$given instanceof GivenName && $latin?->secondaryIdentifiers() === [self::NO_FORENAME];
        parent::__construct(
            $family,
            $given,
            $latin instanceof IcaoPersonName && $mononymOverride
                ? IcaoPersonName::fromParts($latin->primaryIdentifier())
                : $latin,
        );
    }

    #[Override]
    protected function overridePolicy(LatinPersonNameBuilder $builder): AnyIcaoSpelling
    {
        return new AnyIcaoSpelling();
    }
}
