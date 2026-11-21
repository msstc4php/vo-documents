<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao\Mrz;

use MSSTC4PHP\ValueObject\Documents\Internal\MrzField;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzLayout;
use Override;

/**
 * TD2 size official travel document: two lines of 36 characters, long document numbers supported.
 *
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p6_cons_en.pdf ICAO Doc 9303 Part 6,
 *      8th ed. (2021), §4.2.2, §4.2.4
 */
final class Td2 extends MachineReadableZone
{
    public const int LINES = 2;

    public const int LINE_LENGTH = 36;

    #[Override]
    protected static function layout(): MrzLayout
    {
        return new MrzLayout(
            lines: self::LINES,
            lineLength: self::LINE_LENGTH,
            documentCodePattern: '/^(?:[CI][A-UW-Z<]|A[A-HJ-UW-Z<])$/D',
            documentCode: new MrzField(1, 1, 2),
            issuingState: new MrzField(1, 3, 3),
            name: new MrzField(1, 6, 31),
            documentNumber: new MrzField(2, 1, 9),
            documentNumberCheck: new MrzField(2, 10, 1),
            longDocumentNumber: true,
            nationality: new MrzField(2, 11, 3),
            birthDate: new MrzField(2, 14, 6),
            birthDateCheck: new MrzField(2, 20, 1),
            sex: new MrzField(2, 21, 1),
            expiryDate: new MrzField(2, 22, 6),
            expiryDateCheck: new MrzField(2, 28, 1),
            optionalData: new MrzField(2, 29, 7),
            compositeCheck: new MrzField(2, 36, 1),
            compositeData: [new MrzField(2, 1, 10), new MrzField(2, 14, 7), new MrzField(2, 22, 14)],
            masked: [new MrzField(2, 1, 10), new MrzField(2, 14, 7), new MrzField(2, 29, 8)],
        );
    }
}
