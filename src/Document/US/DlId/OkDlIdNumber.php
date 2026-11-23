<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Oklahoma: "an alphabetic character followed by nine numeric digits", or "any nine-digit number" a licensee may
 * request. The source states no ID-card numbering, so the class covers the driver licence only.
 *
 * @see https://apps.ok.gov/dps/courts/external/documents/DPS.pdf Oklahoma DPS, Online Court Reporting System, field 2
 * @see
 *      https://oklahoma.gov/content/dam/service-oklahoma/intranet/job-resources/sops/a--driver-license/A.01%20-%20Original%20Issuance%20-%20Class%20D%20and%20State%20ID.pdf
 *      Service Oklahoma, SOP A.01
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class OkDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::OK;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{9}', '[0-9]{9}');
    }
}
