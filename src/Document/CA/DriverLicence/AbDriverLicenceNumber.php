<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * Alberta: "Driver's Licence No. ######-### provides the unique DL#" (Service Alberta, RDN 01/2024), nine digits;
 * `formatted()` gives that display form, `123456-789`. Shorter historical numbers are not accepted: no Alberta text
 * says how they are padded. The photo ID card number is not covered.
 *
 * @see https://www.alberta.ca/system/files/sa-registrar-decision-notification-01-2024.pdf Service Alberta, RDN 01/2024
 * @see https://www.alberta.ca/system/files/sa-registrar-decision-notification-05-2025.pdf Service Alberta, RDN 05/2025
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
final class AbDriverLicenceNumber extends AbstractDriverLicenceNumber
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
        return Jurisdiction::AB;
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
