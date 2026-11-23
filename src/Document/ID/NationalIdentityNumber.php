<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\ID;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Nomor Induk Kependudukan, the 16 digits Dukcapil describes: "6 digit pertama merupakan kode wilayah" (province,
 * regency or city, district), "6 digit kedua merupakan tanggal, bulan, dan tahun kelahiran" with "khusus untuk
 * perempuan tanggal lahirnya ditambah angka 40", and the last four a serial number issued by SIAK. A woman's day is
 * 41-71, so {@see Sex()} is read from it; a day outside 1-31 and 41-71 or a month outside 1-12 is refused, as it fits
 * neither coding. A day the month does not have (30 February) is accepted: no official text says such a NIK is never
 * issued (docs/sources/ID.md). The year has two digits and no century rule is published, so there is no birth date. The
 * region code is a plain string, not an ISO 3166-2 guess, and is not checked against the Kemendagri code list. Since
 * July 2024 Korlantas issues the driving-licence number as the NIK, and DJP uses it as the NPWP of an individual
 * resident. No display form is quoted, so there is no `formatted()`; `masked()` keeps the last four digits (no
 * official masking rule): `************0001`.
 *
 * @see https://dukcapil.kemendagri.go.id/blog/read/yuk-simak-ini-perbedaan-nik-dan-nit Ditjen Dukcapil
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/ID.md "NIK"
 */
final class NationalIdentityNumber extends AbstractStringValueObject implements HasSex, Maskable
{
    public const int MAX_LENGTH = 16;

    private const string DOCUMENT = 'Indonesian national identity number';

    private const int WOMAN_DAY_OFFSET = 40;

    /** `DDMM`: a man's day 01-31 or a woman's 41-71, then a month 01-12. */
    private const string DAY_AND_MONTH = '/^(?:0[1-9]|[12][0-9]|3[01]|4[1-9]|[56][0-9]|7[01])(?:0[1-9]|1[0-2])$/D';

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

        if (\preg_match(self::DAY_AND_MONTH, \substr($digits, 6, 4)) !== 1) {
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
