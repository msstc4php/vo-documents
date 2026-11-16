<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Maine: "Driver's License Number (7 digits)". The source states no ID-card numbering, so the class covers the driver
 * licence only.
 *
 * @see https://apps1.web.maine.gov/cgi-bin/bmv/drc/step1.pl Maine BMV, Driver's Record Check Service
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class MeDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Me;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{7}');
    }
}
