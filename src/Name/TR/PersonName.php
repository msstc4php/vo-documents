<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\TR;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use Override;

/**
 * Family name and given name(s). The derived Latin form is FAMILY<<GIVEN through the ICAO multinational Latin table:
 * `İ`, `ı`, `I` and `i` all become `I`, `Ş` → `S`, `Ğ` → `G`, `Ö` → `OE` (`Işık İnci` → `ISIK<<INCI`, `Öztürk Ayşe` →
 * `OEZTUERK<<AYSE`). Override policy {@see TransliterationVariant}: no Turkish rule lets a passport keep another Latin
 * spelling, so an override must be another ICAO spelling of both parts, such as `ORNEK<<ZEYNEP` (the passport
 * specimen's MRZ for `Örnek Zeynep`); there is no hyphen to store, and an override equal to the derived form is not
 * kept. See docs/sources/TR.md "Names".
 *
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
            'Invalid Turkish person name (the Latin form is not a transliteration of the name).',
        );
    }
}
