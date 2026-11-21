<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao\Mrz;

use MSSTC4PHP\ValueObject\Documents\Internal\MrzField;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzLayout;
use Override;

/**
 * TD1 size official travel document (identity card): three lines of 30 characters, long document numbers supported.
 * `optionalData()` is the optional data of the upper line, {@see secondOptionalData()} that of the middle line.
 *
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p5_cons_en.pdf ICAO Doc 9303 Part 5,
 *      8th ed. (2021), §4.2.2, §4.2.4
 */
final class Td1 extends MachineReadableZone
{
    public const int LINES = 3;

    public const int LINE_LENGTH = 30;

    public function secondOptionalData(): string
    {
        return \rtrim(new MrzField(2, 19, 11)->of(\explode("\n", $this->value)), '<');
    }

    #[Override]
    protected static function layout(): MrzLayout
    {
        return new MrzLayout(
            lines: self::LINES,
            lineLength: self::LINE_LENGTH,
            documentCodePattern: '/^(?:[CI][A-UW-Z<]|A[A-HJ-UW-Z<])$/D',
            documentCode: new MrzField(1, 1, 2),
            issuingState: new MrzField(1, 3, 3),
            name: new MrzField(3, 1, 30),
            documentNumber: new MrzField(1, 6, 9),
            documentNumberCheck: new MrzField(1, 15, 1),
            longDocumentNumber: true,
            nationality: new MrzField(2, 16, 3),
            birthDate: new MrzField(2, 1, 6),
            birthDateCheck: new MrzField(2, 7, 1),
            sex: new MrzField(2, 8, 1),
            expiryDate: new MrzField(2, 9, 6),
            expiryDateCheck: new MrzField(2, 15, 1),
            optionalData: new MrzField(1, 16, 15),
            compositeCheck: new MrzField(2, 30, 1),
            compositeData: [
                new MrzField(1, 6, 25),
                new MrzField(2, 1, 7),
                new MrzField(2, 9, 7),
                new MrzField(2, 19, 11),
            ],
            masked: [new MrzField(1, 6, 25), new MrzField(2, 1, 7), new MrzField(2, 19, 12)],
        );
    }
}
