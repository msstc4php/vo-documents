<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Document\GB\ChiNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/GB.md "CHI number, Scotland"; no example is published, so the numbers are
 * synthetic. `from()` checks the format of the check digit only; `hasValidCheckDigit()` reports the PHS checks.
 */
#[CoversClass(ChiNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
#[UsesClass(TwoDigitYearDate::class)]
final class ChiNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'compact' => ['1203456719', '1203456719'];
        yield 'any check digit (format only)' => ['1203456710', '1203456710'];
        yield 'spaced' => ['120345 6719', '1203456719'];
        yield '29 February, year 00' => ['2902001234', '2902001234'];
        yield '29 February, year 96' => ['2902961234', '2902961234'];
        yield '31 December' => ['3112991234', '3112991234'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = ChiNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string, Sex, int, int, non-empty-string}>
     */
    public static function encodedData(): iterable
    {
        yield 'odd ninth digit, male' => ['1203456719', Sex::Male, 12, 3, '45'];
        yield 'even ninth digit, female' => ['1203456729', Sex::Female, 12, 3, '45'];
        yield 'zero ninth digit, female' => ['0101000000', Sex::Female, 1, 1, '00'];
        yield 'nine as ninth digit, male' => ['3112999991', Sex::Male, 31, 12, '99'];
    }

    #[DataProvider('encodedData')]
    public function testReadsSexAndBirthDate(string $input, Sex $sex, int $day, int $month, string $year): void
    {
        $number = ChiNumber::from($input);

        self::assertSame($sex, $number->sex());
        self::assertSame($day, $number->birthDay());
        self::assertSame($month, $number->birthMonth());
        self::assertSame($year, $number->birthYearDigits());
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function checkDigits(): iterable
    {
        yield 'modulus 11 only' => ['1203456719', true];
        yield 'modulus 11 only, every weight in play' => ['2811883215', true];
        yield 'every weight in play, check digit changed' => ['2811883216', false];
        yield 'modulus 10 only' => ['1203456718', true];
        yield 'neither' => ['1203456710', false];
        yield 'modulus 11 of 11, read as 0' => ['1203450060', true];
        yield 'modulus 11 of 10, never a digit; 0 fails' => ['1203450010', false];
        yield 'modulus 11 of 10; the modulus 10 digit passes' => ['1203450018', true];
    }

    #[DataProvider('checkDigits')]
    public function testReportsTheCheckDigitAndStillAcceptsAFailure(string $input, bool $valid): void
    {
        $number = ChiNumber::from($input);

        self::assertSame($valid, $number->hasValidCheckDigit());
        self::assertSame($input, $number->value);
    }

    public function testMasksAllButTheLastFourDigits(): void
    {
        self::assertSame('******6719', ChiNumber::from('1203456719')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $date = 'Invalid Scottish CHI number (invalid birth date).';

        yield 'day 00' => ['0003456719', $date];
        yield 'day 32' => ['3201456719', $date];
        yield 'month 00' => ['1200456719', $date];
        yield 'month 13' => ['1213456719', $date];
        yield '30 February' => ['3002961234', $date];
        yield '29 February, year 01' => ['2902011234', $date];
        yield '31 June' => ['3106451234', $date];
        yield 'nine digits (leading zero lost)' => ['101456719', 'Invalid Scottish CHI number (wrong length).'];
        yield 'eleven digits' => ['12034567190', 'Invalid Scottish CHI number (wrong length).'];
        yield 'letter' => ['120345671X', 'Invalid Scottish CHI number (invalid character).'];
        yield 'empty' => ['', 'Invalid Scottish CHI number (wrong length).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(ChiNumber::from(...), $input, $message);
    }
}
