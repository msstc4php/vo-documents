<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Nebraska: "one letter (A,B,C,E,G,H, or V) followed by eight numbers" (the letter set is enforced). One numbering for
 * the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/NebraskaRIDEFactSheet.pdf USCIS, E-Verify RIDE
 *      fact sheet "Nebraska Driver's License and ID Card Information"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class NeDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 9;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NE;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[ABCEGHV][0-9]{8}');
    }
}
