<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * A birth date whose year has no century: only a date impossible in every century is refused, so 29 February is
 * accepted when the two-digit year is divisible by four (`00` included, as 2000).
 *
 * @internal
 */
final class TwoDigitYearDate
{
    private const array DAYS_IN_MONTH = [1 => 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    public static function exists(int $day, int $month, int $yearDigits): bool
    {
        if (!isset(self::DAYS_IN_MONTH[$month])) {
            return false;
        }

        $lastDay = self::DAYS_IN_MONTH[$month];
        if ($month === 2 && $yearDigits % 4 !== 0) {
            $lastDay = 28;
        }

        return $day >= 1 && $day <= $lastDay;
    }
}
