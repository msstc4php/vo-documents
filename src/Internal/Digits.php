<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

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
        $digits = self::strip($input);
        if (\mb_strlen($digits, 'UTF-8') !== $length) {
            throw self::invalidLength($digits, $document);
        }

        if (\strspn($digits, '0123456789') !== $length) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        return Text::nonEmpty($digits);
    }

    /**
     * @param string $normalized the input with the separators removed
     * @param non-empty-string $document the document's name in the exception message
     */
    public static function invalidLength(string $normalized, string $document): InvalidDocumentNumberException
    {
        $count = \mb_strlen($normalized, 'UTF-8');

        return new InvalidDocumentNumberException(
            $count > self::MAX_REPORTED_LENGTH
                ? \sprintf('Invalid %s (too long).', $document)
                : \sprintf('Invalid %s (%d characters).', $document, $count),
        );
    }
}
