<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * New York: "the 9-digit "ID" number printed in the upper portion of your driver license, learner permit, or non-driver
 * ID". One numbering for the driver licence and the ID card.
 *
 * @see https://dmv.ny.gov/transaction-message/information-about-transaction-entries NY DMV, "Information about
 *      Transaction Entries"
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class NyDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NY;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}');
    }
}
