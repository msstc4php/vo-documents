<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Washington: `WDL` and "9 randomly chosen alphabetical characters and numbers" (from 2018), or the legacy 12-character
 * PIC ("The first seven characters must be alphabetic or "*"; the eighth and ninth characters must be numeric, the
 * tenth character must be numeric or "*", and the eleventh and twelfth characters may be alphabetic or numeric"); the
 * PIC's self-checking digit is unpublished and not verified. One numbering for the driver licence and the ID card.
 *
 * @see http://www.dol.wa.gov/realid.html WA DOL, REAL ID information
 * @see https://www.wsp.wa.gov/wp-content/uploads/2023/10/08_licensing.pdf Washington State Patrol, ACCESS Operations
 *      Manual, chapter 8
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class WaDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 12;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::WA;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('WDL[0-9A-Z]{9}', '[A-Z*]{7}[0-9]{2}[0-9*][0-9A-Z]{2}');
    }
}
