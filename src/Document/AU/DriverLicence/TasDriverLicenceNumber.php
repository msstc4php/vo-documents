<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use Override;

/**
 * Tasmania licence number: "6-8 alphanumeric characters" (the sample prints a placeholder).
 *
 * @see https://immi.homeaffairs.gov.au/help-text/eplus/Pages/elp-h3330.aspx Department of Home Affairs, "Australian
 *      driver licence", "Tasmania"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/AU.md "Driving licence numbers"
 */
final class TasDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    public const int MAX_LENGTH = 8;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::TAS;
    }

    #[Override]
    protected static function format(): NumberFormat
    {
        return new NumberFormat('0-9A-Z', 6, 8);
    }
}
