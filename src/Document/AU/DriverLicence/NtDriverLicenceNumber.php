<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use Override;

/**
 * Northern Territory licence number: "up to 10 numeric characters" (samples `1564102`, `1164381`).
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence", "Northern Territory"
 * @see docs/sources/AU.md "Driving licence numbers"
 */
final class NtDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    public const int MAX_LENGTH = 10;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NT;
    }

    #[Override]
    protected static function format(): NumberFormat
    {
        return new NumberFormat('0-9', 1, 10);
    }
}
