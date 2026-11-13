<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Document\GB\GreatBritainDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/GB.md "Driving licence number, Great Britain"; `MORGA 657054 SM 9IJ` is the DVLA
 * INS57P example.
 */
#[CoversClass(GreatBritainDriverLicenceNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
#[UsesClass(TwoDigitYearDate::class)]
final class GreatBritainDriverLicenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'INS57P example' => ['MORGA 657054 SM 9IJ', 'MORGA657054SM9IJ'];
        yield 'compact, lower case' => ['morga657054sm9ij', 'MORGA657054SM9IJ'];
        yield 'short surname padded with 9' => ['MAN99 803236 J9 AB1', 'MAN99803236J9AB1'];
        yield 'one-letter surname' => ['O9999 712310 AB 123', 'O9999712310AB123'];
        yield 'NBSP and hyphens' => ["MORGA\u{00A0}657054-SM-9IJ", 'MORGA657054SM9IJ'];
        yield '29 February of a year divisible by four' => ['SMITH 802290 JA 1AB', 'SMITH802290JA1AB'];
        yield '29 February, year 00' => ['SMITH 002290 JA 1AB', 'SMITH002290JA1AB'];
        yield '29 February, year 12' => ['SMITH 102292 JA 1AB', 'SMITH102292JA1AB'];
        yield '29 February, woman' => ['SMITH 852290 JA 1AB', 'SMITH852290JA1AB'];
        yield '31 December, woman' => ['SMITH 862315 JA 1AB', 'SMITH862315JA1AB'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = GreatBritainDriverLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testReadsTheExample(): void
    {
        $number = GreatBritainDriverLicenceNumber::from('MORGA657054SM9IJ');

        self::assertSame(Sex::Female, $number->sex());
        self::assertSame('64', $number->birthYearDigits());
        self::assertSame(7, $number->birthMonth());
        self::assertSame(5, $number->birthDay());
    }

    /**
     * @return iterable<string, array{string, Sex, non-empty-string, int, int}>
     */
    public static function birthData(): iterable
    {
        yield 'man, March' => ['MAN99803236J9AB1', Sex::Male, '86', 3, 23];
        yield 'woman, March' => ['MAN99853236J9AB1', Sex::Female, '86', 3, 23];
        yield 'woman, October' => ['MAN99860016J9AB1', Sex::Female, '86', 10, 1];
        yield 'man, December' => ['MAN99812316J9AB1', Sex::Male, '86', 12, 31];
        yield 'woman, December' => ['MAN99862316J9AB1', Sex::Female, '86', 12, 31];
        yield 'man, January' => ['MAN99801016J9AB1', Sex::Male, '86', 1, 1];
        yield 'woman, January' => ['MAN99851016J9AB1', Sex::Female, '86', 1, 1];
    }

    #[DataProvider('birthData')]
    public function testReadsSexAndBirthDate(string $input, Sex $sex, string $year, int $month, int $day): void
    {
        $number = GreatBritainDriverLicenceNumber::from($input);

        self::assertSame($sex, $number->sex());
        self::assertSame($year, $number->birthYearDigits());
        self::assertSame($month, $number->birthMonth());
        self::assertSame($day, $number->birthDay());
    }

    public function testFormatsAndMasksInTheLeafletLayout(): void
    {
        $number = GreatBritainDriverLicenceNumber::from('MORGA657054SM9IJ');

        self::assertSame('MORGA 657054 SM 9IJ', $number->formatted());
        self::assertSame('***** ****** *M 9IJ', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Great Britain driving licence number (invalid character).';
        $date = 'Invalid Great Britain driving licence number (invalid birth date).';

        yield '9 inside the surname' => ['MA9N9 803236 J9 AB1', $character];
        yield 'surname of 9 only' => ['99999 803236 J9 AB1', $character];
        yield 'letter after the 9 padding' => ['MAN9A 803236 J9 AB1', $character];
        yield 'digit first' => ['1AN99 803236 J9 AB1', $character];
        yield 'short surname without padding' => ['ABCD 123456 AB 123X', $character];
        yield 'digit in the surname' => ['MAN19 803236 J9 AB1', $character];
        yield 'letter in the date' => ['MORGA 6570A4 SM 9IJ', $character];
        yield 'first initial 9' => ['MORGA 657054 9M 9IJ', $character];
        yield 'digit as second initial' => ['MORGA 657054 S1 9IJ', $character];
        yield 'symbol' => ['MORGA 657054 SM 9I*', $character];
        yield 'month 00' => ['MORGA 600054 SM 9IJ', $date];
        yield 'month 13' => ['MORGA 613054 SM 9IJ', $date];
        yield 'month 50' => ['MORGA 650054 SM 9IJ', $date];
        yield 'month 63' => ['MORGA 663054 SM 9IJ', $date];
        yield 'day 00' => ['MORGA 657004 SM 9IJ', $date];
        yield 'day 32' => ['MORGA 657324 SM 9IJ', $date];
        yield '31 April' => ['MORGA 604314 SM 9IJ', $date];
        yield '30 February' => ['MORGA 602304 SM 9IJ', $date];
        yield '29 February of a year not divisible by four' => ['SMITH 702290 JA 1AB', $date];
        yield '29 February, year 21' => ['SMITH 202291 JA 1AB', $date];
        yield '29 February, woman, year 85' => ['SMITH 852295 JA 1AB', $date];
        yield 'with the issue number' => [
            'MORGA 657054 SM 9IJ 01',
            'Invalid Great Britain driving licence number (18 characters).',
        ];
        yield 'seventeen characters' => [
            'MORGA 657054 SM 9IJ 0',
            'Invalid Great Britain driving licence number (17 characters).',
        ];
        yield 'fifteen characters' => [
            'MORGA 657054 SM 9I',
            'Invalid Great Britain driving licence number (15 characters).',
        ];
        yield 'empty' => ['', 'Invalid Great Britain driving licence number (0 characters).'];
        yield 'far too long' => [\str_repeat('A', 50), 'Invalid Great Britain driving licence number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(GreatBritainDriverLicenceNumber::from(...), $input, $message);
    }
}
