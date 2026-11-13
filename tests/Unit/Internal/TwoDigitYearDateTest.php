<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwoDigitYearDate::class)]
final class TwoDigitYearDateTest extends TestCase
{
    /**
     * @return iterable<string, array{int, int, int, bool}>
     */
    public static function dates(): iterable
    {
        yield '1 January' => [1, 1, 50, true];
        yield '31 January' => [31, 1, 50, true];
        yield '32 January' => [32, 1, 50, false];
        yield 'day 0' => [0, 1, 50, false];
        yield '30 April' => [30, 4, 50, true];
        yield '31 April' => [31, 4, 50, false];
        yield '31 December' => [31, 12, 50, true];
        yield 'month 0' => [1, 0, 50, false];
        yield 'month 13' => [1, 13, 50, false];
        yield '28 February' => [28, 2, 1, true];
        yield '29 February, year 01' => [29, 2, 1, false];
        yield '29 February, year 04' => [29, 2, 4, true];
        yield '29 February, year 00' => [29, 2, 0, true];
        yield '30 February, year 04' => [30, 2, 4, false];
    }

    #[DataProvider('dates')]
    public function testExists(int $day, int $month, int $year, bool $exists): void
    {
        self::assertSame($exists, TwoDigitYearDate::exists($day, $month, $year));
    }
}
