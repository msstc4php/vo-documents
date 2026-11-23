<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Michigan: "The number begins with the first letter of the individual's last name" (not verified), followed by the
 * "twelve-digit number from your driver's license or ID". One numbering for the driver licence and the ID card.
 *
 * @see https://www.michigan.gov/sos/faqs/resources/milogin-and-online-accounts Michigan SOS, "MiLogin and online
 *      accounts"
 * @see https://mdhhs-pres-prod.michigan.gov/olmweb/ex/foc/340X1.pdf Michigan FIA, Friend of the Court Manual FOC 340X1
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class MiDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 13;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::MI;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{12}');
    }
}
