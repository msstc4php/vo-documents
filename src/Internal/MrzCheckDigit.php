<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;

/**
 * Check digit of ICAO Doc 9303 Part 3 §4.9: modulus 10, weights 7-3-1 repeated, `<` is 0, A-Z are 10-35.
 *
 * @internal
 */
final class MrzCheckDigit
{
    private const array WEIGHTS = [7, 3, 1];

    private function __construct()
    {
    }

    public static function of(string $data): int
    {
        $sum = 0;
        foreach (\str_split($data) as $position => $character) {
            $sum += self::value($character) * self::WEIGHTS[$position % 3];
        }

        return $sum % 10;
    }

    public static function matches(string $data, string $digit): bool
    {
        return \preg_match('/^[0-9]$/', $digit) === 1 && self::of($data) === (int) $digit;
    }

    private static function value(string $character): int
    {
        return match (true) {
            $character === '<' => 0,
            \preg_match('/^[0-9]$/', $character) === 1 => (int) $character,
            \preg_match('/^[A-Z]$/', $character) === 1 => \ord($character) - 55,
            default => throw new LogicException('Not an MRZ character.'),
        };
    }
}
