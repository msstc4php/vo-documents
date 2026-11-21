<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao\Mrz;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidMrzException;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzCheckDigit;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzField;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzLayout;
use Override;

/**
 * Passport and other TD3 size document: two lines of 44 characters. The personal number (`optionalData()`) has its
 * own check digit, which may be `<` when the field is blank.
 *
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p4_cons_en.pdf ICAO Doc 9303 Part 4,
 *      8th ed. (2021), §4.2.2, §4.2.4
 */
final class Td3 extends MachineReadableZone
{
    public const int LINES = 2;

    public const int LINE_LENGTH = 44;

    #[Override]
    protected static function layout(): MrzLayout
    {
        return new MrzLayout(
            lines: self::LINES,
            lineLength: self::LINE_LENGTH,
            documentCodePattern: '/^P[A-Z<]$/D',
            documentCode: new MrzField(1, 1, 2),
            issuingState: new MrzField(1, 3, 3),
            name: new MrzField(1, 6, 39),
            documentNumber: new MrzField(2, 1, 9),
            documentNumberCheck: new MrzField(2, 10, 1),
            longDocumentNumber: false,
            nationality: new MrzField(2, 11, 3),
            birthDate: new MrzField(2, 14, 6),
            birthDateCheck: new MrzField(2, 20, 1),
            sex: new MrzField(2, 21, 1),
            expiryDate: new MrzField(2, 22, 6),
            expiryDateCheck: new MrzField(2, 28, 1),
            optionalData: new MrzField(2, 29, 14),
            compositeCheck: new MrzField(2, 44, 1),
            compositeData: [new MrzField(2, 1, 10), new MrzField(2, 14, 7), new MrzField(2, 22, 22)],
            masked: [new MrzField(2, 1, 10), new MrzField(2, 14, 7), new MrzField(2, 29, 16)],
        );
    }

    #[Override]
    protected static function assertFormatSpecific(array $lines): void
    {
        $personalNumber = new MrzField(2, 29, 14)->of($lines);
        $check = new MrzField(2, 43, 1)->of($lines);
        $blank = \strspn($personalNumber, '<') === 14;

        if (($check !== '<' || !$blank) && !MrzCheckDigit::matches($personalNumber, $check)) {
            throw new InvalidMrzException('Invalid ICAO MRZ check digit.');
        }
    }
}
