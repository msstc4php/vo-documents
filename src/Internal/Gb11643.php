<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use DateTimeImmutable;
use DateTimeZone;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;

/**
 * The 18-character layout of GB 11643-1999 that the permanent-residence number of 2023 shares: 17 digits, an 8-digit
 * `YYYYMMDD` birth date and a check character of ISO 7064 MOD 11-2.
 *
 * @internal
 */
final class Gb11643
{
    private const int LENGTH = 18;

    private const int CHECK_INDEX = 17;

    private const int BIRTH_DATE_OFFSET = 6;

    /**
     * @param non-empty-string $document the document's name in the exception message
     *
     * @return non-empty-string 17 digits and a digit or `X`
     *
     * @throws InvalidDocumentNumberException on a wrong length or a character the layout does not allow
     */
    public static function normalize(string $input, string $document): string
    {
        $number = new NumberFormat('0-9X', self::LENGTH, self::LENGTH)->parse($input, $document);
        if (\strspn($number, '0123456789', 0, self::CHECK_INDEX) !== self::CHECK_INDEX) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        return $number;
    }

    /**
     * @param non-empty-string $number a normalised 18-character number
     * @param non-empty-string $document the document's name in the exception message
     *
     * @throws InvalidDocumentNumberException when the birth date is not a calendar date
     */
    public static function assertBirthDate(string $number, string $document): void
    {
        [$year, $month, $day] = self::birthDateParts($number);
        if (!\checkdate((int) $month, (int) $day, (int) $year)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', $document));
        }
    }

    /**
     * @param non-empty-string $number a normalised 18-character number
     * @param non-empty-string $document the document's name in the exception message
     *
     * @throws InvalidDocumentNumberException
     */
    public static function assertCheckCharacter(string $number, string $document): void
    {
        if (Checksum::iso7064Mod11Dash2(\substr($number, 0, self::CHECK_INDEX)) !== $number[self::CHECK_INDEX]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', $document));
        }
    }

    /**
     * @param non-empty-string $number a number {@see assertBirthDate()} accepted
     */
    public static function birthDate(string $number): DateTimeImmutable
    {
        [$year, $month, $day] = self::birthDateParts($number);
        // A tampered unserialized value would otherwise reach the DateTimeImmutable message.
        $digits = \strspn($number, '0123456789', self::BIRTH_DATE_OFFSET, 8);
        if ($digits !== 8 || !\checkdate((int) $month, (int) $day, (int) $year)) {
            throw new LogicException('GB 11643 number validated by assertBirthDate().');
        }

        return new DateTimeImmutable($year . '-' . $month . '-' . $day, new DateTimeZone('UTC'));
    }

    /**
     * @return array{string, string, string} year, month and day as written
     */
    private static function birthDateParts(string $number): array
    {
        return [
            \substr($number, self::BIRTH_DATE_OFFSET, 4),
            \substr($number, self::BIRTH_DATE_OFFSET + 4, 2),
            \substr($number, self::BIRTH_DATE_OFFSET + 6, 2),
        ];
    }
}
