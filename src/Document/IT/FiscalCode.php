<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\IT;

use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Codice fiscale of a natural person, 16 characters (DM 23 dicembre 1976): three letters of the surname, three of the
 * name, two digits of the birth year, the month letter, the birth day (plus 40 for women), the birth-place code (a
 * letter and three digits) and the check letter, verified with the decree's tables. Any of the seven digits may be
 * replaced by its omocodia letter (`0` = `L` … `9` = `V`); the fields are read through that table and the check letter
 * is computed on the characters as written. The year has no century, so there is no `HasBirthDate`; a day that is
 * no date of the month in any century (`30` February, `29` February of a year not divisible by four) is refused.
 * No display form is published; `masked()` keeps the last four characters (no official masking rule):
 * `************501U`. The eleven-digit provisional number (art. 10) is not covered.
 *
 * @see https://www.gazzettaufficiale.it/eli/id/1999/04/27/099A3188/sg DM 23 dicembre 1976 "Sistemi di codificazione
 *      dei soggetti da iscrivere all'anagrafe tributaria", artt. 2-7, reprinted as allegato 2 of the DM 19 aprile 1999
 *      (GU n. 97 del 27-4-1999, S.O. n. 83)
 * @see docs/sources/IT.md "Codice fiscale"
 */
final class FiscalCode extends AbstractStringValueObject implements HasSex, Maskable
{
    private const string DOCUMENT = 'Italian fiscal code';

    /** Art. 6: a digit, or the letter that replaces it in an omocode. */
    private const array DIGITS = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'L' => 0, 'M' => 1, 'N' => 2, 'P' => 3, 'Q' => 4, 'R' => 5, 'S' => 6, 'T' => 7, 'U' => 8, 'V' => 9,
    ];

    /** Positions (0-based) of the seven digits, art. 6. */
    private const array DIGIT_POSITIONS = [6, 7, 9, 10, 12, 13, 14];

    /** Art. 5: the month letters. */
    private const array MONTHS = [
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'H' => 6, 'L' => 7, 'M' => 8, 'P' => 9, 'R' => 10, 'S' => 11,
        'T' => 12,
    ];

    private const array DAYS_IN_MONTH = [1 => 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    /** Art. 7: [value in an even position (table 1), value in an odd position (table 2)]. */
    private const array CHECK_VALUES = [
        '0' => [0, 1], '1' => [1, 0], '2' => [2, 5], '3' => [3, 7], '4' => [4, 9], '5' => [5, 13], '6' => [6, 15],
        '7' => [7, 17], '8' => [8, 19], '9' => [9, 21], 'A' => [0, 1], 'B' => [1, 0], 'C' => [2, 5], 'D' => [3, 7],
        'E' => [4, 9], 'F' => [5, 13], 'G' => [6, 15], 'H' => [7, 17], 'I' => [8, 19], 'J' => [9, 21], 'K' => [10, 2],
        'L' => [11, 4], 'M' => [12, 18], 'N' => [13, 20], 'O' => [14, 11], 'P' => [15, 3], 'Q' => [16, 6],
        'R' => [17, 8], 'S' => [18, 12], 'T' => [19, 14], 'U' => [20, 16], 'V' => [21, 10], 'W' => [22, 22],
        'X' => [23, 25], 'Y' => [24, 24], 'Z' => [25, 23],
    ];

    /** Art. 7: the letter of each remainder, 0 = A … 25 = Z. */
    private const string CHECK_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    #[Override]
    public function sex(): Sex
    {
        return $this->digitAt(9) >= 4 ? Sex::Female : Sex::Male;
    }

    /**
     * @return non-empty-string the tens and units of the birth year (art. 5), omocodia letters read as digits
     */
    public function birthYearDigits(): string
    {
        return $this->digitAt(6) . $this->digitAt(7);
    }

    /**
     * @return int<1, 12>
     */
    public function birthMonth(): int
    {
        return self::MONTHS[$this->value[8]];
    }

    /**
     * @return int<1, 31>
     */
    public function birthDay(): int
    {
        return \max(1, \min(31, $this->dayOfMonth($this->value)));
    }

    /**
     * @return non-empty-string the code of the Italian municipality or foreign state of birth, a letter and three
     *                          digits (art. 5, "Codice dei comuni d'Italia" / "Codice degli Stati esteri"), e.g. "H501"
     */
    public function birthPlaceCode(): string
    {
        return $this->value[11] . $this->digitAt(12) . $this->digitAt(13) . $this->digitAt(14);
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $code = new NumberFormat('0-9A-Z', 16, 16)->parse($value, self::DOCUMENT);
        if (!$this->hasShape($code)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        $this->assertBirthDate($code);

        if ($code[15] !== self::CHECK_LETTERS[$this->checkValue($code)]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $code;
    }

    private function hasShape(string $code): bool
    {
        foreach (self::DIGIT_POSITIONS as $position) {
            if (!isset(self::DIGITS[$code[$position]])) {
                return false;
            }
        }

        return \ctype_upper(\substr($code, 0, 6))
            && \ctype_upper($code[8])
            && \ctype_upper($code[11])
            && \ctype_upper($code[15]);
    }

    private function assertBirthDate(string $code): void
    {
        $message = \sprintf('Invalid %s (invalid birth month).', self::DOCUMENT);
        $month = self::MONTHS[$code[8]] ?? throw new InvalidDocumentNumberException($message);
        $year = $this->digitIn($code, 6) * 10 + $this->digitIn($code, 7);
        $lastDay = $month === 2 && $year % 4 !== 0 ? 28 : self::DAYS_IN_MONTH[$month];
        $day = $this->dayOfMonth($code);
        if ($this->digitIn($code, 9) > 7 || $day === 0 || $day > $lastDay) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth day).', self::DOCUMENT));
        }
    }

    /**
     * Art. 5: the day, plus 40 for women; the tens digit is 0-3 for men and 4-7 for women.
     */
    private function dayOfMonth(string $code): int
    {
        return $this->digitIn($code, 9) % 4 * 10 + $this->digitIn($code, 10);
    }

    /**
     * Art. 7: the values of the first fifteen characters (1st, 3rd … 15th through table 2, the others through
     * table 1), summed, modulo 26.
     */
    private function checkValue(string $code): int
    {
        $sum = 0;
        for ($index = 0; $index < 15; $index++) {
            $sum += self::CHECK_VALUES[$code[$index]][($index + 1) % 2];
        }

        return $sum % 26;
    }

    /**
     * @return int<0, 9>
     */
    private function digitAt(int $position): int
    {
        return $this->digitIn($this->value, $position);
    }

    /**
     * @return int<0, 9>
     */
    private function digitIn(string $code, int $position): int
    {
        return self::DIGITS[$code[$position]];
    }
}
