<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

/**
 * Categories and subcategories of the Russian driving licence, in the order the licence prints them: "A, A1, B, B1, C,
 * C1, D, D1, BE, CE, C1E, DE, D1E, M, Tm, Tb". The values are the Latin designations printed on the licence.
 *
 * @see docs/sources/RU.md "Driving licence" — MVD Order of 13.05.2009 No. 365, appendix 1, section 9; Federal Law of
 *      10.12.1995 No. 196-FZ, Art. 25 §1
 */
enum DriverLicenceCategory: string
{
    case A = 'A';
    case A1 = 'A1';
    case B = 'B';
    case B1 = 'B1';
    case C = 'C';
    case C1 = 'C1';
    case D = 'D';
    case D1 = 'D1';
    case BE = 'BE';
    case CE = 'CE';
    case C1E = 'C1E';
    case DE = 'DE';
    case D1E = 'D1E';
    case M = 'M';
    case Tm = 'Tm';
    case Tb = 'Tb';
}
