<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents;

enum Sex: string
{
    case Female = 'female';
    case Male = 'male';

    /**
     * A third value a document encodes: the CURP `X` of RENAPO for non-binary persons (INE DERFE 2023; see
     * docs/sources/MX.md "CURP") and the MRZ filler `<` ("< = unspecified", ICAO Doc 9303 Parts 4-6; see
     * docs/sources/ICAO.md 5.1).
     */
    case Unspecified = 'unspecified';
}
