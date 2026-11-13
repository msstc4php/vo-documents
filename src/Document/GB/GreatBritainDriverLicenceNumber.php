<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Driver number of a DVLA licence (England, Scotland and Wales), 16 characters, `MORGA 657054 SM 9IJ`: the first five
 * letters of the surname padded with `9` (`MAN99`); the birth date as year decade, month (plus 50 for a woman), day
 * and year unit; the first two initials (`9` for a missing second one); three randomly generated check characters,
 * which are not verified. The date must exist; the year has no century, so there is no `HasBirthDate` and 29 February
 * is accepted when the two-digit year is divisible by four. The licence issue number printed after it is not part of
 * the number. `masked()` keeps the last four characters of `formatted()` (no official masking rule):
 * `***** ****** *M 9IJ`.
 *
 * @see https://assets.publishing.service.gov.uk/media/688c6e8f6c7eb66caea94e04/ins57p-information-on-driving-licences.pdf
 *      DVLA, INS57P "Information on driving licences" (7/25), "How to check your driver number"
 * @see docs/sources/GB.md "Driving licence number, Great Britain"
 */
final class GreatBritainDriverLicenceNumber extends AbstractStringValueObject implements HasSex, Maskable
{
    private const string DOCUMENT = 'Great Britain driving licence number';

    private const int FEMALE_MONTH_OFFSET = 50;

    #[Override]
    public function sex(): Sex
    {
        return $this->monthField() > self::FEMALE_MONTH_OFFSET ? Sex::Female : Sex::Male;
    }

    /**
     * @return non-empty-string the first and last digits of the birth-date field; the number holds no century
     */
    public function birthYearDigits(): string
    {
        return $this->value[5] . $this->value[10];
    }

    /**
     * @return int<1, 12>
     */
    public function birthMonth(): int
    {
        // parse() has validated the month; the clamp only narrows the type for PHPStan.
        return \max(1, \min(12, $this->monthField() % self::FEMALE_MONTH_OFFSET));
    }

    /**
     * @return int<1, 31>
     */
    public function birthDay(): int
    {
        return \max(1, \min(31, (int) \substr($this->value, 8, 2)));
    }

    /**
     * @return non-empty-string e.g. "MORGA 657054 SM 9IJ"
     */
    public function formatted(): string
    {
        return $this->layout($this->value);
    }

    #[Override]
    public function masked(): string
    {
        return $this->layout(Mask::keepLastCharacters(Text::nonEmpty($this->value), 4));
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 16, 16)->parse($value, self::DOCUMENT);
        // The eleven characters after the surname field have a fixed shape, so the field is five characters long.
        if (\preg_match('/^[A-Z]+9*[0-9]{6}[A-Z][A-Z9][A-Z0-9]{3}$/', $number) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        $monthField = (int) \substr($number, 6, 2);
        $month = $monthField > self::FEMALE_MONTH_OFFSET ? $monthField - self::FEMALE_MONTH_OFFSET : $monthField;
        $year = (int) ($number[5] . $number[10]);
        if (!TwoDigitYearDate::exists((int) \substr($number, 8, 2), $month, $year)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', self::DOCUMENT));
        }

        return $number;
    }

    private function monthField(): int
    {
        return (int) \substr($this->value, 6, 2);
    }

    /**
     * @return non-empty-string
     */
    private function layout(string $value): string
    {
        return \sprintf(
            '%s %s %s %s',
            \substr($value, 0, 5),
            \substr($value, 5, 6),
            \substr($value, 11, 2),
            \substr($value, 13),
        );
    }
}
