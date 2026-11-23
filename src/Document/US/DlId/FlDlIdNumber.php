<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * Florida: "one letter followed by 12 numeric characters" (the letter is the first of the last name, not verified);
 * `formatted()` gives the printed form `Z123-456-78-901-0` (FLHSMV example). One numbering for the driver licence and
 * the ID card.
 *
 * @see https://www.e-verify.gov/sites/default/files/everify/factsheets/FloridaRIDEFactSheet.pdf USCIS, E-Verify RIDE
 *      fact sheet "Florida Driver's License and ID Card Information"
 * @see https://mydmvportal.flhsmv.gov/home/en/publicweb/dlcheck FLHSMV, "Driver License Check"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/US.md "DL/ID numbers"
 */
final class FlDlIdNumber extends AbstractDlIdNumber
{
    public const int MAX_LENGTH = 13;

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
        return Jurisdiction::FL;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{12}');
    }

    #[Override]
    protected static function layout(string $compact): string
    {
        return \sprintf(
            '%s-%s-%s-%s-%s',
            \substr($compact, 0, 4),
            \substr($compact, 4, 3),
            \substr($compact, 7, 2),
            \substr($compact, 9, 3),
            \substr($compact, 12),
        );
    }
}
