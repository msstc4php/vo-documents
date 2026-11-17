<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use DateTimeImmutable;
use DateTimeZone;
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

    private const int SEVENTH = 6;

    /** First year of the century that each value of the seventh digit stands for. */
    private const array CENTURY = [
        '1' => 1900, '2' => 1900, '5' => 1900, '6' => 1900,
        '3' => 2000, '4' => 2000, '7' => 2000, '8' => 2000,
        '9' => 1800, '0' => 1800,
    ];

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
        if (!\str_contains($sevenths, $number[self::SEVENTH])) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        if (!\checkdate((int) \substr($number, 2, 2), (int) \substr($number, 4, 2), self::year($number))) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', $document));
        }

        return $number;
    }

    /**
     * @param non-empty-string $number a number {@see normalize()} accepted
     */
    public static function birthDate(string $number): DateTimeImmutable
    {
        return new DateTimeImmutable(
            \sprintf(
                '%04d-%s-%s',
                self::year($number),
                \substr($number, 2, 2),
                \substr($number, 4, 2),
            ),
            new DateTimeZone('UTC'),
        );
    }

    /**
     * @param non-empty-string $number a number {@see normalize()} accepted
     */
    public static function sex(string $number): Sex
    {
        return \in_array($number[self::SEVENTH], ['1', '3', '5', '7', '9'], true) ? Sex::Male : Sex::Female;
    }

    private static function year(string $number): int
    {
        return self::CENTURY[$number[self::SEVENTH]] + (int) \substr($number, 0, 2);
    }
}
