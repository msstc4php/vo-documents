<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;

/**
 * @internal
 *
 * Every method expects at least one ASCII digit and nothing else, and throws \LogicException otherwise.
 *
 * Sources: ISO/IEC 7812-1 (Luhn), ISO/IEC 7064:2003 (MOD 11-2), Verhoeff 1969 (the scheme UIDAI's "A UID Numbering
 * Scheme" recommends for the Aadhaar number)
 */
final class Checksum
{
    /** @var list<list<int>> multiplication table of the dihedral group D5 */
    private const array VERHOEFF_MULTIPLICATION = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
        [2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
        [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
        [4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
        [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
        [6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
        [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
        [8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
        [9, 8, 7, 6, 5, 4, 3, 2, 1, 0],
    ];

    /** @var list<list<int>> position-dependent permutations of the digits, applied cyclically (period 8) */
    private const array VERHOEFF_PERMUTATION = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
        [5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
        [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
        [9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
        [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
        [2, 7, 9, 3, 8, 0, 6, 4, 1, 5],
        [7, 0, 4, 6, 9, 1, 3, 2, 5, 8],
    ];

    /**
     * Whether the last digit of `$digits` is the Verhoeff check digit of the digits before it.
     */
    public static function verhoeff(string $digits): bool
    {
        self::assertDigits($digits);

        $check = 0;
        foreach (\str_split(\strrev($digits)) as $position => $digit) {
            $check = self::VERHOEFF_MULTIPLICATION[$check][self::VERHOEFF_PERMUTATION[$position % 8][(int) $digit]];
        }

        return $check === 0;
    }

    public static function luhn(string $digits): bool
    {
        self::assertDigits($digits);

        $sum = 0;
        $double = false;
        for ($i = \strlen($digits) - 1; $i >= 0; $i--) {
            $digit = (int) $digits[$i];
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double;
        }

        return $sum % 10 === 0;
    }

    /**
     * @param list<int> $weights
     * @param positive-int $modulus
     */
    public static function weightedMod(string $digits, array $weights, int $modulus): int
    {
        self::assertDigits($digits);
        if (\strlen($digits) !== \count($weights)) {
            throw new LogicException('Digit count must equal the weight count.');
        }

        $sum = 0;
        foreach ($weights as $index => $weight) {
            $sum += (int) $digits[$index] * $weight;
        }

        return $sum % $modulus;
    }

    /**
     * @return non-empty-string the check character, 0-9 or X
     */
    public static function iso7064Mod11Dash2(string $digits): string
    {
        self::assertDigits($digits);

        $product = 0;
        for ($i = 0, $length = \strlen($digits); $i < $length; $i++) {
            $product = (($product + (int) $digits[$i]) * 2) % 11;
        }

        $check = (12 - $product) % 11;

        return $check === 10 ? 'X' : (string) $check;
    }

    private static function assertDigits(string $digits): void
    {
        if (\preg_match('/^[0-9]+$/D', $digits) !== 1) {
            throw new LogicException('Checksum input must be one or more ASCII digits.');
        }
    }
}
