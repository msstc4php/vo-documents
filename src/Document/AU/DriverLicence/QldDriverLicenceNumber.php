<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use Override;

/**
 * Queensland licence number (the customer reference number): "8-9 numeric characters" (sample `123 456 789`).
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence", "Queensland"
 * @see docs/sources/AU.md "Driving licence numbers"
 */
final class QldDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Qld;
    }

    #[Override]
    protected static function format(): string
    {
        return '[0-9]{8,9}';
    }
}
