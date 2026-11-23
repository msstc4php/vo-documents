<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * Wyoming: "nine numbers- six numbers followed by a hyphen and three numbers"; `formatted()` gives that form,
 * `123456-789`. One numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/WyomingRIDEFactSheet.pdf USCIS, E-Verify RIDE
 *      fact sheet "Wyoming Driver's License and ID Card Information"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class WyDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 9;

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return self::layout(Text::nonEmpty($this->value));
    }

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::WY;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}');
    }

    #[Override]
    protected static function layout(string $compact): string
    {
        return \sprintf('%s-%s', \substr($compact, 0, 6), \substr($compact, 6));
    }
}
