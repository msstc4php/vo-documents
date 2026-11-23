<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Vermont: "8 characters long. The first 7 characters will always be a digit, and the final character can be a digit or
 * letter" of A, F, J, K, L, Q, R, T, V, X, Y (enforced; the older check digit is unpublished and not verified). One
 * numbering for the driver licence and the ID card.
 *
 * @see https://dmv.vermont.gov/sites/dmv/files/documents/Law%20Enforcement%20Changes%202025.pdf Vermont DMV, "Law
 *      Enforcement Changes" (2025), PID Format
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class VtDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::VT;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{7}[0-9AFJKLQRTVXY]');
    }
}
