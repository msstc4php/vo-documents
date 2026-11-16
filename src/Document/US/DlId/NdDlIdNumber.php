<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * North Dakota: "three letters followed by six numbers". One numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/NorthDakotaRIDEFactSheet.pdf USCIS, E-Verify
 *      RIDE fact sheet "North Dakota Driver's License and ID Card Information"
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class NdDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Nd;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z]{3}[0-9]{6}');
    }
}
