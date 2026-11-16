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
    public const string SEPARATORS = " -\t\r\n\u{00A0}\u{2007}\u{202F}";

    /**
     * Above this the message says "too long": a count of 100 or more would be a three-digit run.
     */
    public const int MAX_REPORTED_LENGTH = 40;

    public static function strip(string $input, string $separators = self::SEPARATORS): string
    {
        $characters = \mb_str_split($separators, 1, 'UTF-8');

        return \str_replace($characters, '', $input);
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
