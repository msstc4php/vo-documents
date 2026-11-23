<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Iowa: "nine alphanumeric characters (letters and numbers)". One numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/IowaRIDEFactSheet.pdf USCIS, E-Verify RIDE fact
 *      sheet "Iowa Driver's License and ID Card Information"
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class IaDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 9;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::IA;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9A-Z]{9}');
    }
}
