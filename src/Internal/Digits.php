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
     * @throws InvalidDocumentNumberException with the document name and the character count only
     */
    public static function exactly(string $input, int $length, string $document): string
    {
        $digits = self::strip($input);

        if ($digits === '' || \preg_match('/^[0-9]{' . $length . '}$/D', $digits) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (%d characters).', $document, \mb_strlen($digits, 'UTF-8')));
        }

        return $digits;
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
