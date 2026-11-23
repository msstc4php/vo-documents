<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * British Columbia: an integer of up to eight digits written "padded with leading zeros" (ICBC: "Existing DLNs: add
 * additional leading zero to increase length to 8 characters"), so `347631`, `0347631` and `00347631` are one number,
 * stored as eight digits. Cards show seven or eight digits. The BCID card has a separate number, not covered.
 *
 * @see https://partners.icbc.com/assets/3r0rg6eeZ7PkeCIz3K9Usq/dln-technical-guidelines.pdf ICBC, "Driver's Licence
 *      Number Project — Technical information for external stakeholders" v1.1, §5.1, §7
 * @see https://partners.icbc.com/assets/6cjn4yP78X3AOu0g5vLn1y/dln-faqs.pdf ICBC, "Driver's licence number (DLN)
 *      changes" FAQ
 * @see docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
final class BcDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    public const int MAX_LENGTH = 8;

    private const int LENGTH = 8;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::BC;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes(
            '[0-9]',
            '[0-9]{2}',
            '[0-9]{3}',
            '[0-9]{4}',
            '[0-9]{5}',
            '[0-9]{6}',
            '[0-9]{7}',
            '[0-9]{8}',
        );
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        return Text::nonEmpty(\str_pad($normalized, self::LENGTH, '0', \STR_PAD_LEFT));
    }
}
