<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Illinois: driver licence a letter and eleven digits ("12-position alphanumeric figure"); ID card (PID) eleven digits
 * and a letter ("a PID carries the single alpha character in the last position"). The class accepts both. Both encode
 * name, sex and birth date by an unpublished formula; the quoted digit ranges contradict each other and are not
 * enforced.
 *
 * @see https://isp.illinois.gov/LawEnforcement/GetFile/de672e4e-5074-484d-a82f-cbfc20145c54 Illinois State Police,
 *      LEADS Operating Manual, Drivers License
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class IlDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Il;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{11}', '[0-9]{11}[A-Z]');
    }
}
