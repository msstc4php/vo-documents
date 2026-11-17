<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use Closure;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;

/**
 * @internal
 */
final class Digits
{
    public const string SEPARATORS = " -\t\r\n\u{00A0}\u{2007}\u{202F}\u{3000}";

    /**
     * Above this the message says "too long": a count of 100 or more would be a three-digit run.
     */
    public const int MAX_REPORTED_LENGTH = 40;

    /**
     * Fullwidth digits and letters (letters upper-cased) and Arabic-Indic digits, mapped to ASCII.
     */
    private const array FOLD_MAP = [
        "\u{FF10}" => '0',
        "\u{FF11}" => '1',
        "\u{FF12}" => '2',
        "\u{FF13}" => '3',
        "\u{FF14}" => '4',
        "\u{FF15}" => '5',
        "\u{FF16}" => '6',
        "\u{FF17}" => '7',
        "\u{FF18}" => '8',
        "\u{FF19}" => '9',
        "\u{0660}" => '0',
        "\u{0661}" => '1',
        "\u{0662}" => '2',
        "\u{0663}" => '3',
        "\u{0664}" => '4',
        "\u{0665}" => '5',
        "\u{0666}" => '6',
        "\u{0667}" => '7',
        "\u{0668}" => '8',
        "\u{0669}" => '9',
        "\u{06F0}" => '0',
        "\u{06F1}" => '1',
        "\u{06F2}" => '2',
        "\u{06F3}" => '3',
        "\u{06F4}" => '4',
        "\u{06F5}" => '5',
        "\u{06F6}" => '6',
        "\u{06F7}" => '7',
        "\u{06F8}" => '8',
        "\u{06F9}" => '9',
        "\u{FF21}" => 'A',
        "\u{FF22}" => 'B',
        "\u{FF23}" => 'C',
        "\u{FF24}" => 'D',
        "\u{FF25}" => 'E',
        "\u{FF26}" => 'F',
        "\u{FF27}" => 'G',
        "\u{FF28}" => 'H',
        "\u{FF29}" => 'I',
        "\u{FF2A}" => 'J',
        "\u{FF2B}" => 'K',
        "\u{FF2C}" => 'L',
        "\u{FF2D}" => 'M',
        "\u{FF2E}" => 'N',
        "\u{FF2F}" => 'O',
        "\u{FF30}" => 'P',
        "\u{FF31}" => 'Q',
        "\u{FF32}" => 'R',
        "\u{FF33}" => 'S',
        "\u{FF34}" => 'T',
        "\u{FF35}" => 'U',
        "\u{FF36}" => 'V',
        "\u{FF37}" => 'W',
        "\u{FF38}" => 'X',
        "\u{FF39}" => 'Y',
        "\u{FF3A}" => 'Z',
        "\u{FF41}" => 'A',
        "\u{FF42}" => 'B',
        "\u{FF43}" => 'C',
        "\u{FF44}" => 'D',
        "\u{FF45}" => 'E',
        "\u{FF46}" => 'F',
        "\u{FF47}" => 'G',
        "\u{FF48}" => 'H',
        "\u{FF49}" => 'I',
        "\u{FF4A}" => 'J',
        "\u{FF4B}" => 'K',
        "\u{FF4C}" => 'L',
        "\u{FF4D}" => 'M',
        "\u{FF4E}" => 'N',
        "\u{FF4F}" => 'O',
        "\u{FF50}" => 'P',
        "\u{FF51}" => 'Q',
        "\u{FF52}" => 'R',
        "\u{FF53}" => 'S',
        "\u{FF54}" => 'T',
        "\u{FF55}" => 'U',
        "\u{FF56}" => 'V',
        "\u{FF57}" => 'W',
        "\u{FF58}" => 'X',
        "\u{FF59}" => 'Y',
        "\u{FF5A}" => 'Z',
    ];

    /**
     * Maps fullwidth digits and letters and Arabic-Indic digits to ASCII (letters upper-cased), so a number typed on
     * a CJK or Arabic keyboard reads as the ASCII one. Names do not use it: they refuse compatibility characters.
     */
    public static function fold(string $input): string
    {
        return \strtr($input, self::FOLD_MAP);
    }

    public static function strip(string $input, string $separators = self::SEPARATORS): string
    {
        $characters = \mb_str_split($separators, 1, 'UTF-8');

        return \str_replace($characters, '', self::fold($input));
    }

    /**
     * Strips the separators and requires exactly `$length` ASCII digits.
     *
     * @param positive-int $length
     * @param non-empty-string $document the document's name in the exception message
     *
     * @return non-empty-string
     *
     * @throws InvalidDocumentNumberException "(N characters)" for a wrong length of up to
     *                                        Digits::MAX_REPORTED_LENGTH characters, "(too long)" above it,
     *                                        "(invalid character)" for a right length with
     *                                        a character other than a digit
     */
    public static function exactly(string $input, int $length, string $document): string
    {
        return self::exactlyStripped(self::strip($input), $length, $document);
    }

    /**
     * {@see exactly()} for an input whose separators are already removed.
     *
     * @param positive-int $length
     * @param non-empty-string $document the document's name in the exception message
     *
     * @return non-empty-string
     *
     * @throws InvalidDocumentNumberException as {@see exactly()}
     */
    public static function exactlyStripped(string $digits, int $length, string $document): string
    {
        if (self::length($digits) !== $length) {
            throw self::invalidLength($digits, $document);
        }

        if (\strspn($digits, '0123456789') !== $length) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        return Text::nonEmpty($digits);
    }

    /**
     * Strips the separators, upper-cases ASCII letters and requires a length `$allowsLength` accepts; the characters
     * are left to the caller.
     *
     * @param Closure(int): bool $allowsLength
     * @param non-empty-string $document the document's name in the exception message
     *
     * @throws InvalidDocumentNumberException as {@see invalidLength()} when the length is refused
     */
    public static function upperCasedOfLength(string $input, Closure $allowsLength, string $document): string
    {
        $normalized = \strtoupper(self::strip($input));
        if (!$allowsLength(self::length($normalized))) {
            throw self::invalidLength($normalized, $document);
        }

        return $normalized;
    }

    /**
     * The number of characters of `$input` once the separators are removed.
     *
     * @return non-negative-int
     */
    public static function strippedLength(string $input): int
    {
        return self::length(self::strip($input));
    }

    /**
     * @param string $normalized the input with the separators removed
     * @param non-empty-string $document the document's name in the exception message
     */
    public static function invalidLength(string $normalized, string $document): InvalidDocumentNumberException
    {
        $count = self::length($normalized);

        return new InvalidDocumentNumberException(
            $count > self::MAX_REPORTED_LENGTH
                ? \sprintf('Invalid %s (too long).', $document)
                : \sprintf('Invalid %s (%d characters).', $document, $count),
        );
    }

    /**
     * @return non-negative-int
     */
    private static function length(string $value): int
    {
        return \mb_strlen($value, 'UTF-8');
    }
}
