<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * New Jersey: "a letter followed by 14 digits". The source states no ID-card numbering, so the class covers the driver
 * licence only.
 *
 * @see https://emvc.state.nj.us/mvc/MVC_SUAAV_WS/html/AVSHelp.html NJ MVC, MyMVC AVS help
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class NjDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 15;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NJ;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{14}');
    }
}
