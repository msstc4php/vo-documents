<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Maryland: "one letter followed by twelve numbers" (the MVA customer ID `MD` + 11 digits is not a card number). One
 * numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/MarylandRIDEFactSheet.pdf USCIS, E-Verify RIDE
 *      fact sheet "Maryland Driver's License and ID Card Information"
 * @see https://egov.maryland.gov/mva/idvr/support/userguide MDOT MVA, IDVR web user guide
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class MdDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 13;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::MD;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{12}');
    }
}
