<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use Override;

/**
 * Australian Capital Territory licence number: "up to 10 numeric characters" (sample `1234567890`).
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence", "Australian Capital Territory"
 * @see docs/sources/AU.md "Driving licence numbers"
 */
final class ActDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Act;
    }

    #[Override]
    protected static function format(): string
    {
        return '[0-9]{1,10}';
    }
}
