<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * New Mexico: "This 9-digit number can be found at the top of your license". The source states no ID-card numbering, so
 * the class covers the driver licence only.
 *
 * @see https://secure.mvd.newmexico.gov/RenewalServices/default.aspx NM MVD, Online Driver History Records Service
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class NmDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 9;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NM;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}');
    }
}
