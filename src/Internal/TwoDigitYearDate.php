<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * A birth date whose year has no century. {@see exists()}: only a date impossible in every century is refused, so 29
 * February is accepted when the two-digit year is divisible by four (`00` included, as 2000). {@see inRanges()}: only
 * the month 1-12 and the day 1-31, for the numbers whose sources do not make the digits a calendar date (README
 * "Birth dates").
 *
 * @internal
 */
final class TwoDigitYearDate
{
    private const array DAYS_IN_MONTH = [1 => 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    private function __construct()
    {
    }

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

    public static function inRanges(int $day, int $month): bool
    {
        return $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31;
    }
}
