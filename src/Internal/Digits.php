<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use Closure;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use Normalizer;

/**
 * Number normalisation. Besides whitespace and the ASCII hyphen, the separators are the hyphen look-alikes a CJK
 * keyboard types with fullwidth digits (ISA writes its format with `ｰ`) and the invisible bidi marks a number picks
 * up when pasted from right-to-left text.
 *
 * @internal
 */
final class Digits
{
    public const string SEPARATORS = " -\t\r\n\u{00A0}\u{2007}\u{202F}\u{3000}"
        . "\u{FF0D}\u{FF70}\u{30FC}\u{2010}\u{2011}\u{2212}"
        . "\u{200E}\u{200F}\u{061C}\u{202A}\u{202B}\u{202C}\u{202D}\u{202E}\u{2066}\u{2067}\u{2068}\u{2069}";

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

    private function __construct()
    {
    }

    /**
     * Fullwidth letters are upper-cased. Names do not fold: they refuse compatibility characters.
     */
    public static function fold(string $input): string
    {
        return \strtr($input, self::FOLD_MAP);
    }

    /**
     * Strips the separators and composes (NFC); `ñ` becomes `Ñ`, the one letter beyond ASCII of the MX keys. No other
     * character is case-mapped (`ß`, `ﬁ`, `ı` stay as they are, for the caller's pattern to refuse): full Unicode case
     * mapping would turn them into letters the official alphabet has.
     */
    public static function composedWithCapitalEnye(string $input): string
    {
        $composed = Normalizer::normalize(self::strip($input), Normalizer::FORM_C);

        return \is_string($composed) ? \str_replace('ñ', 'Ñ', $composed) : '';
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
     * @throws InvalidDocumentNumberException "(wrong length)", or "(invalid character)" for a right length with a
     *                                        character other than a digit
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
            throw self::invalidLength($document);
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
            throw self::invalidLength($document);
        }

        return $normalized;
    }

    /**
     * @return non-negative-int
     */
    public static function strippedLength(string $input): int
    {
        return self::length(self::strip($input));
    }

    /**
     * The message names no length: not even the count of characters of the input is reported.
     *
     * @param non-empty-string $document the document's name in the exception message
     */
    public static function invalidLength(string $document): InvalidDocumentNumberException
    {
        return new InvalidDocumentNumberException(\sprintf('Invalid %s (wrong length).', $document));
    }

    /**
     * @return non-negative-int
     */
    private static function length(string $value): int
    {
        return \mb_strlen($value, 'UTF-8');
    }
}
