<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Colorado: "your 9-digit driver license or ID number". One numbering for the driver licence and the ID card.
 *
 * @see https://dmv.colorado.gov/sites/default/files/documents/DR_2330_e_wo.pdf Colorado DMV, form DR 2330
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class CoDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 9;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::CO;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}');
    }
}
