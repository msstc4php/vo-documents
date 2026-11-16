<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Hawaii: the application form's licence number field `H _ _ _ _ _ _ _ _` (a fixed `H` and eight characters). The
 * source states no ID-card numbering, so the class covers the driver licence only.
 *
 * @see
 *      https://www4.honolulu.gov/docushare/dsweb/Get/Document-325980/State%20of%20Hawaii%20Driver_s%20License%20Application.pdf
 *      State of Hawaii Driver's License Application
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class HiDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Hi;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes([
            'H[0-9A-Z]{8}' => 9,
        ]);
    }
}
