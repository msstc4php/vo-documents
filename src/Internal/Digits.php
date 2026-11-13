<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;
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
        $characters = \mb_str_split($separators);

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
     * @throws InvalidDocumentNumberException with the document name and the character count (or "too long") only
     */
    public static function exactly(string $input, int $length, string $document): string
    {
        $digits = self::strip($input);

        if (\preg_match('/^[0-9]{' . $length . '}$/D', $digits) !== 1) {
            throw self::invalidLength($digits, $document);
        }

        return self::nonEmpty($digits);
    }

    /**
     * @param string $normalized the input with the separators removed
     * @param non-empty-string $document the document's name in the exception message
     */
    public static function invalidLength(string $normalized, string $document): InvalidDocumentNumberException
    {
        $count = \mb_strlen($normalized, 'UTF-8');

        return new InvalidDocumentNumberException($count > self::MAX_REPORTED_LENGTH ? \sprintf('Invalid %s (too long).', $document) : \sprintf('Invalid %s (%d characters).', $document, $count));
    }

    /**
     * Types a stored, already validated digit string for PHPStan.
     *
     * @return non-empty-string
     */
    public static function nonEmpty(string $digits): string
    {
        if ($digits === '') {
            throw new LogicException('A stored document number is never empty.');
        }

        return $digits;
    }
}
