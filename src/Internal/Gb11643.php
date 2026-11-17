<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use DateTimeImmutable;
use DateTimeZone;
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
        if (\strspn($number, '0123456789', 0, 17) !== 17) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        return $number;
    }

    /**
     * @param non-empty-string $number a normalised 18-character number
     * @param non-empty-string $document the document's name in the exception message
     *
     * @throws InvalidDocumentNumberException when the date at `$offset` is not a calendar date
     */
    public static function assertBirthDate(string $number, int $offset, string $document): void
    {
        $year = (int) \substr($number, $offset, 4);
        $month = (int) \substr($number, $offset + 4, 2);
        $day = (int) \substr($number, $offset + 6, 2);
        if (!\checkdate($month, $day, $year)) {
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
        if (Checksum::iso7064Mod11Dash2(\substr($number, 0, 17)) !== $number[17]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', $document));
        }
    }

    /**
     * @param non-empty-string $number a number {@see assertBirthDate()} accepted
     */
    public static function birthDate(string $number, int $offset): DateTimeImmutable
    {
        return new DateTimeImmutable(
            \sprintf(
                '%s-%s-%s',
                \substr($number, $offset, 4),
                \substr($number, $offset + 4, 2),
                \substr($number, $offset + 6, 2),
            ),
            new DateTimeZone('UTC'),
        );
    }
}
