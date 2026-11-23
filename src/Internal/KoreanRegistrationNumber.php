<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use DateTimeImmutable;
use DateTimeZone;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Sex;

/**
 * The 13 digits of the resident and the foreigner registration number: `YYMMDD` and a seventh digit for the sex (and,
 * for a resident, the century) of the birth date. Which digits belong to which number is the caller's. No check digit
 * is verified (numbers issued since 2020-10 carry random digits after the seventh).
 *
 * @internal
 */
final class KoreanRegistrationNumber
{
    private const int LENGTH = 13;

    private const string NOT_NORMALIZED = 'Korean registration number validated by normalize().';

    private const int MONTH_OFFSET = 2;

    private const int DAY_OFFSET = 4;

    private const int SEVENTH_DIGIT_INDEX = 6;

    private const array SEX = [
        '9' => Sex::Male, '0' => Sex::Female, '1' => Sex::Male, '2' => Sex::Female, '3' => Sex::Male, '4' => Sex::Female,
        '5' => Sex::Male, '6' => Sex::Female, '7' => Sex::Male, '8' => Sex::Female,
    ];

    /**
     * Seventh digit => the first year of the birth century, for the resident digits only: no official text gives the
     * century of the foreigner digits 5-8 (docs/sources/KR.md).
     */
    private const array CENTURY = ['9' => 1800, '0' => 1800, '1' => 1900, '2' => 1900, '3' => 2000, '4' => 2000];

    private function __construct()
    {
    }

    /**
     * @param non-empty-string $document the document's name in the exception message
     * @param non-empty-string $sevenths the digits the seventh position may hold
     *
     * @return non-empty-string 13 ASCII digits
     *
     * @throws InvalidDocumentNumberException on a wrong length, a non-digit, a seventh digit outside `$sevenths` or
     *                                        a birth date that does not exist: in the century of the seventh digit
     *                                        where it has one, otherwise in any century ({@see TwoDigitYearDate})
     */
    public static function normalize(string $input, string $document, string $sevenths): string
    {
        $number = Digits::exactly($input, self::LENGTH, $document);
        $seventh = $number[self::SEVENTH_DIGIT_INDEX];
        if (!\str_contains($sevenths, $seventh)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        $exists = isset(self::CENTURY[$seventh])
            ? \checkdate(self::month($number), self::day($number), self::year($number))
            : TwoDigitYearDate::exists(self::day($number), self::month($number), (int) \substr($number, 0, 2));
        if (!$exists) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', $document));
        }

        return $number;
    }

    /**
     * @param non-empty-string $number a number {@see normalize()} accepted
     */
    public static function birthDate(string $number): DateTimeImmutable
    {
        [$year, $month, $day] = [self::year($number), self::month($number), self::day($number)];
        if (!\checkdate($month, $day, $year)) {
            throw new LogicException(self::NOT_NORMALIZED);
        }

        return new DateTimeImmutable(\sprintf('%04d-%02d-%02d', $year, $month, $day), new DateTimeZone('UTC'));
    }

    /**
     * @param non-empty-string $number a number {@see normalize()} accepted
     */
    public static function sex(string $number): Sex
    {
        return self::SEX[self::seventhDigit($number)] ?? throw new LogicException(self::NOT_NORMALIZED);
    }

    /**
     * @param non-empty-string $number a number {@see normalize()} accepted
     *
     * @return non-empty-string the tens and units of the birth year
     */
    public static function yearDigits(string $number): string
    {
        return Text::nonEmpty(\substr($number, 0, 2));
    }

    /**
     * @param non-empty-string $number a number {@see normalize()} accepted
     *
     * @return int<1, 12>
     */
    public static function birthMonth(string $number): int
    {
        $month = self::month($number);

        return $month >= 1 && $month <= 12 ? $month : throw new LogicException(self::NOT_NORMALIZED);
    }

    /**
     * @param non-empty-string $number a number {@see normalize()} accepted
     *
     * @return int<1, 31>
     */
    public static function birthDay(string $number): int
    {
        $day = self::day($number);

        return $day >= 1 && $day <= 31 ? $day : throw new LogicException(self::NOT_NORMALIZED);
    }

    private static function year(string $number): int
    {
        return (self::CENTURY[self::seventhDigit($number)] ?? throw new LogicException(self::NOT_NORMALIZED))
            + (int) \substr($number, 0, 2);
    }

    /**
     * A tampered value (built without parse()) fails with fixed text instead of an "Undefined array key" warning
     * that would show its digit.
     */
    private static function seventhDigit(string $number): string
    {
        return $number[self::SEVENTH_DIGIT_INDEX] ?? throw new LogicException(self::NOT_NORMALIZED);
    }

    private static function month(string $number): int
    {
        return (int) \substr($number, self::MONTH_OFFSET, 2);
    }

    private static function day(string $number): int
    {
        return (int) \substr($number, self::DAY_OFFSET, 2);
    }
}
