<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Kansas: "either Social Security number, Computer Generated number that starts with "K" and 8 numbers, or a 6 digit
 * alpha-numeric number" (an SSN is not checked as one). One numbering for the driver licence and the ID card.
 *
 * @see https://www.ksrevenue.gov/pdf/readingdmvreport.pdf Kansas DOR, "How to Read a Driving Record"
 * @see https://www.kdor.ks.gov/Apps/DLStatus/login.aspx Kansas DOR, Driver's License Status Check
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class KsDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Ks;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}', 'K[0-9]{8}', '[0-9A-Z]{6}');
    }
}
