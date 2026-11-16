<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Pennsylvania: "Your eight (8) digit Driver's License Number (DLN) or Photo Identification Card Number (IDN)". One
 * numbering for the driver licence and the ID card.
 *
 * @see https://www.pa.gov/agencies/dmv/faqs/driver-licensing-faqs/find-your-drivers-license-photo-id-number PennDOT,
 *      "Where to Find Your Driver's License/Photo ID Number"
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class PaDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Pa;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{8}');
    }
}
