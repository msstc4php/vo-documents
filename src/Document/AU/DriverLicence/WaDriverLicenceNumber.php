<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use Override;

/**
 * Western Australia licence number: "7 numeric characters" (sample `6437772`).
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence", "Western Australia"
 * @see docs/sources/AU.md "Driving licence numbers"
 */
final class WaDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Wa;
    }

    #[Override]
    protected static function format(): string
    {
        return '[0-9]{7}';
    }
}
