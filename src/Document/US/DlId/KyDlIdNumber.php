<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Kentucky: "a letter followed by 8 numbers". The source states no ID-card numbering, so the class covers the driver
 * licence only.
 *
 * @see https://apps.transportation.ky.gov/gdl KYTC, Graduated Driver Licensing eligibility
 * @see https://secure.kentucky.gov/dhronline/Records/MoreInformation Commonwealth of Kentucky, Driving History Records
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class KyDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 9;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::KY;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{8}');
    }
}
