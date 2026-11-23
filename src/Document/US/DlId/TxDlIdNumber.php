<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Texas: "Drivers License or ID Card # (8 digits number)". One numbering for the driver licence and the ID card.
 *
 * @see https://www.dps.texas.gov/apps/driverlicense/dlsearch/dlstatus.aspx Texas DPS, "Where's my license or ID?"
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class TxDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::TX;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{8}');
    }
}
