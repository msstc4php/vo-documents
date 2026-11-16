<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Rhode Island: 7 digits, or 8 on licences issued by the upgraded system ("existing 7 digits drivers' licenses will
 * continue to be valid"). The source states no ID-card numbering, so the class covers the driver licence only.
 *
 * @see https://www.ri.gov/DMV/mvr/citizen/ State of Rhode Island, RI DMV Driver Records
 * @see https://dbr.ri.gov/sites/g/files/xkgbur696/files/documents/divisions/insurance/consumers/IndustryAlert2017-2.pdf
 *      RI DBR, Industry Alert 2017-2
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class RiDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Ri;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{7}', '[0-9]{8}');
    }
}
