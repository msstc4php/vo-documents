<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * Wisconsin: "one letter followed by thirteen numbers"; `formatted()` gives the printed form `H300-1987-0162-04`
 * (WisDOT MV4000 manual). One numbering for the driver licence and the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/WisconsinRIDEFactSheet.pdf USCIS, E-Verify RIDE
 *      fact sheet "Wisconsin Driver's License and ID Card Information"
 * @see https://wisconsindot.gov/Documents/dmv/shared/manual-mv4000.pdf WisDOT, MV4000 Law Enforcement Officer's
 *      Instruction Manual, field 29
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class WiDlIdNumber extends AbstractDlIdNumber
{
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
        return Jurisdiction::Wi;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes([
            '[A-Z][0-9]{13}' => 14,
        ]);
    }

    #[Override]
    protected static function layout(string $compact): string
    {
        return \sprintf(
            '%s-%s-%s-%s',
            \substr($compact, 0, 4),
            \substr($compact, 4, 4),
            \substr($compact, 8, 4),
            \substr($compact, 12),
        );
    }
}
