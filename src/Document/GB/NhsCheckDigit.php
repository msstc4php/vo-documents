<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;

/**
 * NHS Data Dictionary, NHS NUMBER, steps 1-5: the first nine digits weighted 10 down to 2, the sum modulo 11
 * subtracted from 11; 11 gives the check digit 0, 10 makes the number invalid.
 *
 * @internal
 */
final class NhsCheckDigit
{
    private const array WEIGHTS = [10, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * @param non-empty-string $digits ten ASCII digits
     */
    public static function isValid(string $digits): bool
    {
        $check = 11 - Checksum::weightedMod(\substr($digits, 0, 9), self::WEIGHTS, 11);

        return $check !== 10 && $check % 11 === (int) $digits[9];
    }
}
