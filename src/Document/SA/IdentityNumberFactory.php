<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\SA;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;

/**
 * Routes a bare Saudi identity number to its class by the first digit, as ZATCA BR-KSA-F-10/F-11 state it: 1 is a
 * national ID number, 2 an iqama number. Any other first digit fits neither and is refused, never guessed; the two
 * classes themselves still accept it. The candidate set (these two classes) is frozen for 1.x.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/SA.md "National ID and iqama"
 */
final class IdentityNumberFactory
{
    private const string NO_CLASS = 'Saudi identity number fits neither the national ID nor the iqama number.';

    private function __construct()
    {
    }

    /**
     * @throws InvalidDocumentNumberException when the number is not ten digits, or its first digit is neither 1 nor 2
     */
    public static function fromNumber(string $number): NationalIdentityNumber|IqamaNumber
    {
        try {
            $national = NationalIdentityNumber::from($number);
        } catch (InvalidDocumentNumberException $exception) {
            throw new InvalidDocumentNumberException(self::NO_CLASS, $exception->getCode(), previous: $exception);
        }

        return match ($national->value[0]) {
            '1' => $national,
            '2' => IqamaNumber::from($national->value),
            default => throw new InvalidDocumentNumberException(self::NO_CLASS),
        };
    }

    /** Null when the number fits neither class; fromNumber() says why. */
    public static function tryFromNumber(string $number): NationalIdentityNumber|IqamaNumber|null
    {
        try {
            return self::fromNumber($number);
        } catch (InvalidValueObjectException) {
            return null;
        }
    }
}
