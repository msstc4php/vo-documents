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
 * (8 FAM 403.1-5(B) c.: "2nd to II"). Case names are the code where it is a PHP name (`JR`, `III`) and the ordinal
 * in words otherwise (`THIRD` for `3RD`). See docs/sources/US.md "Name suffix".
 *
 * @see https://www.aamva.org/getmedia/99ac7057-0f4d-4461-b0a2-3a5532e1b35c/AAMVA-2020-DLID-Card-Design-Standard.pdf
 *      AAMVA DL/ID Card Design Standard (2020), Table 2 ref. g, Table D.4 ref. i
 * @see https://fam.state.gov/fam/08fam/08fam040301.html 8 FAM 403.1-5(B)
 */
enum NameSuffix: string
{
    case JR = 'JR';
    case SR = 'SR';
    case FIRST = '1ST';
    case I = 'I';
    case SECOND = '2ND';
    case II = 'II';
    case THIRD = '3RD';
    case III = 'III';
    case FOURTH = '4TH';
    case IV = 'IV';
    case FIFTH = '5TH';
    case V = 'V';
    case SIXTH = '6TH';
    case VI = 'VI';
    case SEVENTH = '7TH';
    case VII = 'VII';
    case EIGHTH = '8TH';
    case VIII = 'VIII';
    case NINTH = '9TH';
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
            self::FIRST => self::I->value,
            self::SECOND => self::II->value,
            self::THIRD => self::III->value,
            self::FOURTH => self::IV->value,
            self::FIFTH => self::V->value,
            self::SIXTH => self::VI->value,
            self::SEVENTH => self::VII->value,
            self::EIGHTH => self::VIII->value,
            self::NINTH => self::IX->value,
            default => $this->value,
        };
    }
}
