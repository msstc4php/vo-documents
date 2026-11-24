<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Community Health Index (CHI) number of NHS Scotland: ten digits, the birth date `DDMMYY`, two further digits, a sex
 * digit (even for females, odd for males) and a check digit. The date must exist; the year has no century, so there
 * is no `HasBirthDate` and 29 February is accepted when the two-digit year is divisible by four. `from()` checks the
 * format of the check digit only (1.x never refuses a number 1.0 accepted); {@see hasValidCheckDigit()} reports the
 * modulus 11 or modulus 10 check of Public Health Scotland's `chi_check()`. No display form is published; `masked()`
 * keeps the last four digits (no official masking rule): `******6719`.
 *
 * @see https://www.datadictionary.nhs.uk/attributes/community_health_index_number.html NHS Data Model and Dictionary,
 *      COMMUNITY HEALTH INDEX NUMBER (read through the Internet Archive copy of 2025-09-13)
 * @see https://code.publichealthscotland.scot/phsmethods/reference/chi_check.html Public Health Scotland, phsmethods
 *      chi_check()
 * @see https://github.com/Public-Health-Scotland/phsmethods/blob/ee4e49644e90c04162bd6b8ce34ca5c1cb744f3e/R/chi_check.R
 *      the chi_check() source at commit ee4e496
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/GB.md "CHI number, Scotland"
 */
final class ChiNumber extends AbstractStringValueObject implements HasSex, Maskable
{
    public const int MAX_LENGTH = 10;

    private const string DOCUMENT = 'Scottish CHI number';

    #[Override]
    public function sex(): Sex
    {
        return \str_contains('02468', $this->value[8]) ? Sex::Female : Sex::Male;
    }

    /**
     * @return int<1, 31>
     */
    public function birthDay(): int
    {
        // parse() has validated the day; the clamp only narrows the type for PHPStan.
        return \max(1, \min(31, (int) \substr($this->value, 0, 2)));
    }

    /**
     * @return int<1, 12>
     */
    public function birthMonth(): int
    {
        return \max(1, \min(12, (int) \substr($this->value, 2, 2)));
    }

    /**
     * @return non-empty-string the last two digits of the birth year; the number holds no century
     */
    public function birthYearDigits(): string
    {
        return Text::nonEmpty(\substr($this->value, 4, 2));
    }

    /**
     * Whether the tenth digit passes the modulus 11 check (weights 10 to 2 over the first nine digits, 11 read as 0)
     * or the modulus 10 check (the Luhn sum over all ten, issued since August 2026), as PHS `chi_check()` accepts
     * either. Report only: a number that fails both is still accepted.
     */
    public function hasValidCheckDigit(): bool
    {
        return $this->passesModulus11() || Checksum::luhn($this->value);
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 10, self::DOCUMENT);
        $day = (int) \substr($digits, 0, 2);
        $month = (int) \substr($digits, 2, 2);
        if (!TwoDigitYearDate::exists($day, $month, (int) \substr($digits, 4, 2))) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', self::DOCUMENT));
        }

        return $digits;
    }

    private function passesModulus11(): bool
    {
        $check = 11 - Checksum::weightedMod(\substr($this->value, 0, 9), [10, 9, 8, 7, 6, 5, 4, 3, 2], 11);

        return $check % 11 === (int) $this->value[9];
    }
}
