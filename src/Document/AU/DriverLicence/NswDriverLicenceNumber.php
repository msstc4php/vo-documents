<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use Override;

/**
 * New South Wales licence number: "6-8 alphanumeric characters" (sample `01234567`).
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence", "New South Wales"
 * @see docs/sources/AU.md "Driving licence numbers"
 */
final class NswDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Nsw;
    }

    #[Override]
    protected static function format(): NumberFormat
    {
        return new NumberFormat('0-9A-Z', 6, 8);
    }
}
