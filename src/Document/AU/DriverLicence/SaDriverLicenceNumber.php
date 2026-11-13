<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use Override;

/**
 * South Australia licence number: "6 alphanumeric characters" (the sample prints a placeholder).
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence", "South Australia"
 * @see docs/sources/AU.md "Driving licence numbers"
 */
final class SaDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Sa;
    }

    #[Override]
    protected static function format(): string
    {
        return '[0-9A-Z]{6}';
    }
}
