<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Arizona: "one letter followed by eight numbers", or nine digits where the holder's SSN is still printed as the number
 * (ADOT form 99-0121 item 8; not checked as an SSN). One numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/ArizonaRIDEFactSheet.pdf USCIS, E-Verify RIDE
 *      fact sheet "Arizona Driver's License and ID Card Information"
 * @see https://apps.azdot.gov/files/mvd/mvd-forms-lib/99-0121.pdf ADOT MVD, form 99-0121 "Redaction Program"
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class AzDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::AZ;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{8}', '[0-9]{9}');
    }
}
