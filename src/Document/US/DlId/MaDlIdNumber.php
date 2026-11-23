<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Massachusetts: "beginning with an "SA" or an "S" followed by numbers equaling a total of nine characters on your
 * license/ID card". One numbering for the driver licence and the ID card.
 *
 * @see https://www.mass.gov/files/documents/2018/03/21/Drivers_Manual.pdf Massachusetts RMV, Driver's Manual (2/2018)
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class MaDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::MA;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('S[0-9]{8}', 'SA[0-9]{7}');
    }
}
