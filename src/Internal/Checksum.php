<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;

/**
 * @internal
 *
 * Every method expects at least one ASCII digit and nothing else, and throws \LogicException otherwise.
 *
 * Sources: ISO/IEC 7812-1 (Luhn), ISO/IEC 7064:2003 (MOD 11-2)
 */
final class Checksum
{
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
