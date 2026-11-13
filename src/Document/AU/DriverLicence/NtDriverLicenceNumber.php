<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
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
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Nt;
    }

    #[Override]
    protected static function format(): string
    {
        return '[0-9]{1,10}';
    }
}
