<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\US;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;

/**
 * Name suffix, the closed list of the AAMVA "Name suffix" data element (Table 2 ref. g, Table D.4 `DCU`): `JR`, `SR`,
 * and the generations first to ninth, each as an ordinal (`1ST`) or a Roman numeral (`I`). The two spellings of one
 * generation are distinct values, as the cardholder chose them. In the ICAO form the suffix is the last secondary
 * identifier component (Doc 9303 Part 3 §4.6: "represented as components of the secondary identifier(s)" where the
 * State considers it legally part of the name, as 8 FAM 403.1-5(B) does), an ordinal written as its Roman numeral
 * (8 FAM 403.1-5(B) c.: "2nd to II"). See docs/sources/US.md "Name suffix".
 *
 * @see https://www.aamva.org/getmedia/99ac7057-0f4d-4461-b0a2-3a5532e1b35c/AAMVA-2020-DLID-Card-Design-Standard.pdf
 *      AAMVA DL/ID Card Design Standard (2020), Table 2 ref. g, Table D.4 ref. i
 * @see https://fam.state.gov/fam/08fam/08fam040301.html 8 FAM 403.1-5(B)
 */
enum NameSuffix: string
{
    case Junior = 'JR';
    case Senior = 'SR';
    case First = '1ST';
    case I = 'I';
    case Second = '2ND';
    case II = 'II';
    case Third = '3RD';
    case III = 'III';
    case Fourth = '4TH';
    case IV = 'IV';
    case Fifth = '5TH';
    case V = 'V';
    case Sixth = '6TH';
    case VI = 'VI';
    case Seventh = '7TH';
    case VII = 'VII';
    case Eighth = '8TH';
    case VIII = 'VIII';
    case Ninth = '9TH';
    case IX = 'IX';

    /**
     * Reads an AAMVA code in any letter case, surrounding spaces ignored (`jr`, ` III `); a period (`Jr.`) is refused.
     *
     * @throws InvalidPersonNameException when the code is not on the AAMVA list
     */
    public static function fromCode(string $code): self
    {
        return self::tryFrom(\strtoupper(\trim($code)))
            ?? throw new InvalidPersonNameException('Invalid American name suffix.');
    }

    /**
     * @return non-empty-string the ICAO component: `JR`, `SR` or a Roman numeral
     */
    public function icao(): string
    {
        return match ($this) {
            self::First => self::I->value,
            self::Second => self::II->value,
            self::Third => self::III->value,
            self::Fourth => self::IV->value,
            self::Fifth => self::V->value,
            self::Sixth => self::VI->value,
            self::Seventh => self::VII->value,
            self::Eighth => self::VIII->value,
            self::Ninth => self::IX->value,
            default => $this->value,
        };
    }
}
