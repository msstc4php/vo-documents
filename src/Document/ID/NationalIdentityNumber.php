<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\ID;

use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Nomor Induk Kependudukan, the 16 digits Dukcapil describes: "6 digit pertama merupakan kode wilayah" (province,
 * regency or city, district), "6 digit kedua merupakan tanggal, bulan, dan tahun kelahiran" with "khusus untuk
 * perempuan tanggal lahirnya ditambah angka 40", and the last four a serial number issued by SIAK. A woman's day is
 * 41-71, so {@see Sex()} is read from it; a day that is no calendar date (30 February, or 70 for a woman) is refused,
 * as a date that does not exist is no "tanggal lahir". The year has two digits and no century rule is published, so
 * there is no birth date: 29 February is accepted when the two-digit year is divisible by four (`00` included). The
 * region code is a plain string, not an ISO 3166-2 guess, and is not checked against the Kemendagri code list. Since
 * July 2024 Korlantas issues the driving-licence number as the NIK, and DJP uses it as the NPWP of an individual
 * resident. No display form is quoted, so there is no `formatted()`; `masked()` keeps the last four digits (no
 * official masking rule): `************0001`.
 *
 * @see https://dukcapil.kemendagri.go.id/blog/read/yuk-simak-ini-perbedaan-nik-dan-nit Ditjen Dukcapil
 * @see docs/sources/ID.md "NIK"
 */
final class NationalIdentityNumber extends AbstractStringValueObject implements HasSex, Maskable
{
    private const string DOCUMENT = 'Indonesian national identity number';

    private const int WOMAN_DAY_OFFSET = 40;

    /**
     * @return non-empty-string the six digits of the province, regency or city, and district recorded at registration
     */
    public function regionCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 6));
    }

    /**
     * @return int<1, 31>
     */
    public function birthDay(): int
    {
        // parse() has validated the day; the clamp only narrows the type for PHPStan.
        return \max(1, \min(31, $this->dayOf($this->value)));
    }

    /**
     * @return int<1, 12>
     */
    public function birthMonth(): int
    {
        return \max(1, \min(12, (int) \substr($this->value, 8, 2)));
    }

    /**
     * @return non-empty-string the last two digits of the birth year; the number holds no century
     */
    public function birthYearDigits(): string
    {
        return Text::nonEmpty(\substr($this->value, 10, 2));
    }

    #[Override]
    public function sex(): Sex
    {
        return (int) \substr($this->value, 6, 2) > self::WOMAN_DAY_OFFSET ? Sex::Female : Sex::Male;
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 16, self::DOCUMENT);

        if (!TwoDigitYearDate::exists($this->dayOf($digits), (int) \substr($digits, 8, 2), (int) \substr($digits, 10, 2))) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', self::DOCUMENT));
        }

        return $digits;
    }

    private function dayOf(string $digits): int
    {
        $day = (int) \substr($digits, 6, 2);

        return $day > self::WOMAN_DAY_OFFSET ? $day - self::WOMAN_DAY_OFFSET : $day;
    }
}
