<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

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
 * Community Health Index (CHI) number of NHS Scotland: ten digits, the birth date `DDMMYY`, two further digits, a sex
 * digit (even for females, odd for males) and a check digit. The date must exist; the year has no century, so there
 * is no `HasBirthDate` and 29 February is accepted when the two-digit year is divisible by four. Format only for the
 * check digit: no official algorithm text is published, and Public Health Scotland accepts a modulus 11 or, from
 * August 2026, a modulus 10 check digit. No display form is published; `masked()` keeps the last four digits (no
 * official masking rule): `******6719`.
 *
 * @see https://www.datadictionary.nhs.uk/attributes/community_health_index_number.html NHS Data Model and Dictionary,
 *      COMMUNITY HEALTH INDEX NUMBER (read through the Internet Archive copy of 2025-09-13)
 * @see https://code.publichealthscotland.scot/phsmethods/reference/chi_check.html Public Health Scotland, phsmethods
 *      chi_check()
 * @see docs/sources/GB.md "CHI number, Scotland"
 */
final class ChiNumber extends AbstractStringValueObject implements HasSex, Maskable
{
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
}
