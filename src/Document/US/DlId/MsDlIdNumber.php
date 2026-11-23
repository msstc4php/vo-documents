<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Mississippi: "nine numeric characters" (an SSN is not checked as one), or the Rule 4.4 form, a spouse's SSN "followed
 * by a letter of the alphabet". One numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/MississippiRIDEFactSheet.pdf USCIS, E-Verify
 *      RIDE fact sheet "Mississippi Driver's License and ID Card Information"
 * @see https://www.sos.ms.gov/adminsearch/ACCode/00000383c.pdf Mississippi Administrative Code, Title 31 Part 1
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class MsDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 10;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::MS;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}', '[0-9]{9}[A-Z]');
    }
}
