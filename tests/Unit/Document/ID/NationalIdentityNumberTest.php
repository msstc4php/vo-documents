<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\ID;

use MSSTC4PHP\ValueObject\Documents\Document\ID\NationalIdentityNumber;
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
 * Expectations follow docs/sources/ID.md "NIK": Dukcapil's structure of the 16 digits; 15 May 1998 is a date of the
 * tests, not an example of Dukcapil.
 */
#[CoversClass(NationalIdentityNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
#[UsesClass(TwoDigitYearDate::class)]
final class NationalIdentityNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'man born 15 May 1998' => ['3171011505980001', '3171011505980001'];
        yield 'woman born 15 May 1998 (day 55)' => ['3171015505980001', '3171015505980001'];
        yield 'spaces' => ['3171 0115 0598 0001', '3171011505980001'];
        yield 'hyphens' => ['317101-150598-0001', '3171011505980001'];
        yield 'NBSP and ideographic space' => ["317101\u{00A0}150598\u{3000}0001", '3171011505980001'];
        yield 'fullwidth digits' => [
            "\u{FF13}\u{FF11}\u{FF17}\u{FF11}\u{FF10}\u{FF11}\u{FF11}\u{FF15}\u{FF10}\u{FF15}\u{FF19}\u{FF18}" .
                "\u{FF10}\u{FF10}\u{FF10}\u{FF11}",
            '3171011505980001',
        ];
        yield 'Arabic-Indic digits' => [
            "\u{0663}\u{0661}\u{0667}\u{0661}\u{0660}\u{0661}\u{0661}\u{0665}\u{0660}\u{0665}\u{0669}\u{0668}" .
                "\u{0660}\u{0660}\u{0660}\u{0661}",
            '3171011505980001',
        ];
        yield '29 February 1996 of a man' => ['3171012902960001', '3171012902960001'];
        yield '29 February 1996 of a woman (day 69)' => ['3171016902960001', '3171016902960001'];
        yield '29 February with year 00 (2000)' => ['3171012902000001', '3171012902000001'];
        yield '31 December of a woman (day 71)' => ['3171017112850001', '3171017112850001'];
        yield 'first of January of a woman (day 41)' => ['3171014101850001', '3171014101850001'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStores(string $input, string $expected): void
    {
        $number = NationalIdentityNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string, Sex, int, int, string, string}>
     */
    public static function fields(): iterable
    {
        yield 'man 15 May 1998' => ['3171011505980001', Sex::Male, 15, 5, '98', '317101'];
        yield 'woman 15 May 1998: day 55 is 15 + 40' => ['3171015505980001', Sex::Female, 15, 5, '98', '317101'];
        yield 'woman day 41 is the 1st' => ['3171014101850001', Sex::Female, 1, 1, '85', '317101'];
        yield 'woman day 71 is the 31st' => ['3171017112850001', Sex::Female, 31, 12, '85', '317101'];
        yield 'woman day 69 is 29 February' => ['3171016902960001', Sex::Female, 29, 2, '96', '317101'];
        yield 'man day 31' => ['3201013112850001', Sex::Male, 31, 12, '85', '320101'];
        yield 'year 00 keeps its leading zero' => ['1101010101000001', Sex::Male, 1, 1, '00', '110101'];
    }

    #[DataProvider('fields')]
    public function testReadsTheFields(
        string $input,
        Sex $sex,
        int $day,
        int $month,
        string $year,
        string $region,
    ): void {
        $number = NationalIdentityNumber::from($input);

        self::assertSame($sex, $number->sex());
        self::assertSame($day, $number->birthDay());
        self::assertSame($month, $number->birthMonth());
        self::assertSame($year, $number->birthYearDigits());
        self::assertSame($region, $number->regionCode());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('************0001', NationalIdentityNumber::from('3171011505980001')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Indonesian national identity number (invalid character).';
        $date = 'Invalid Indonesian national identity number (invalid birth date).';

        yield 'empty' => ['', 'Invalid Indonesian national identity number (wrong length).'];
        yield '15 digits' => ['317101150598000', 'Invalid Indonesian national identity number (wrong length).'];
        yield '17 digits' => ['31710115059800011', 'Invalid Indonesian national identity number (wrong length).'];
        yield 'far too long' => [\str_repeat('1', 41), 'Invalid Indonesian national identity number (wrong length).'];
        yield 'letter' => ['317101150598000X', $character];
        yield 'full stop is no separator' => [
            '3171.0115.0598.0001',
            'Invalid Indonesian national identity number (wrong length).',
        ];
        yield '30 February of a woman: day 70' => ['3171017002980001', $date];
        yield '30 February of a man' => ['3171013002980001', $date];
        yield '29 February 1997' => ['3171012902970001', $date];
        yield '29 February of a woman, 1997 (day 69)' => ['3171016902970001', $date];
        yield '31 April of a woman: day 71' => ['3171017104980001', $date];
        yield '31 April of a man' => ['3171013104980001', $date];
        yield 'day 00' => ['3171010005980001', $date];
        yield 'day 40: a woman born on day 0' => ['3171014005980001', $date];
        yield 'day 32' => ['3171013205980001', $date];
        yield 'day 39' => ['3171013905980001', $date];
        yield 'day 72: 32 + 40' => ['3171017205980001', $date];
        yield 'day 99' => ['3171019905980001', $date];
        yield 'month 00' => ['3171011500980001', $date];
        yield 'month 13' => ['3171011513980001', $date];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): NationalIdentityNumber => NationalIdentityNumber::from($value),
            $input,
            $message,
        );
    }
}
