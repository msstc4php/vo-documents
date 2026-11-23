<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use DateTimeImmutable;
use DateTimeZone;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Sex;

/**
 * The 13 digits of the resident and the foreigner registration number: `YYMMDD` and a digit for the sex and century
 * of the birth date. Which digits belong to which number is the caller's. No check digit is verified (numbers issued
 * since 2020-10 carry random digits after the seventh).
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

    /** Seventh digit => the first year of the birth century and the sex. */
    private const array SEVENTH_DIGIT = [
        '9' => [1800, Sex::Male], '0' => [1800, Sex::Female],
        '1' => [1900, Sex::Male], '2' => [1900, Sex::Female], '5' => [1900, Sex::Male], '6' => [1900, Sex::Female],
        '3' => [2000, Sex::Male], '4' => [2000, Sex::Female], '7' => [2000, Sex::Male], '8' => [2000, Sex::Female],
    ];

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
     *                                        a birth date that does not exist
     */
    public static function normalize(string $input, string $document, string $sevenths): string
    {
        $number = Digits::exactly($input, self::LENGTH, $document);
        if (!\str_contains($sevenths, $number[self::SEVENTH_DIGIT_INDEX])) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        if (!\checkdate(self::month($number), self::day($number), self::year($number))) {
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
        return self::seventhDigit($number)[1];
    }

    private static function year(string $number): int
    {
        return self::seventhDigit($number)[0] + (int) \substr($number, 0, 2);
    }

    /**
     * A stored value is not parsed again on unserialize, so a tampered one fails here with fixed text instead of an
     * "Undefined array key" warning that would show its digit.
     *
     * @return array{int, Sex}
     */
    private static function seventhDigit(string $number): array
    {
        return self::SEVENTH_DIGIT[$number[self::SEVENTH_DIGIT_INDEX] ?? '']
            ?? throw new LogicException(self::NOT_NORMALIZED);
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
