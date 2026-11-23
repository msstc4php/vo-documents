<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Idaho: "two letters followed by six numeric characters followed by one letter", or the "nine numeric characters" of
 * documents issued before 1993. One numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/IdahoRIDEFactSheet.pdf USCIS, E-Verify RIDE fact
 *      sheet "Idaho Driver's License and ID Card Information"
 * @see https://itd.idaho.gov/guide/driver-records-and-suspensions/ Idaho Transportation Department, "Driver Records and
 *      Suspensions"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class IdDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 9;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::ID;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z]{2}[0-9]{6}[A-Z]', '[0-9]{9}');
    }
}
