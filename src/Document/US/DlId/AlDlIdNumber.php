<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Alabama: driver licence 7 or 8 digits ("Alabama will continue to support the existing 7-digit numbers and also the
 * new 8-digit numbers"); non-driver ID card 8 digits ("Alabama currently issues 8-digit non-driver identification
 * cards"). The class accepts both, so a number does not tell which card it is on.
 *
 * @see https://www.mvtrip.alabama.gov/News/ViewArticle/444 ALEA, "Alabama Driver License Number Changes"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class AlDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 8;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::AL;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{7}', '[0-9]{8}');
    }
}
