<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\KR;

use DateTimeZone;
use MSSTC4PHP\ValueObject\Documents\Document\KR\ResidentRegistrationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\KoreanRegistrationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/KR.md "Resident registration number".
 */
#[CoversClass(ResidentRegistrationNumber::class)]
#[CoversClass(KoreanRegistrationNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class ResidentRegistrationNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'man born in the 1900s' => ['9001011234567', '9001011234567'];
        yield 'woman born in the 1900s' => ['9001012234567', '9001012234567'];
        yield 'hyphen' => ['900101-1234567', '9001011234567'];
        yield 'spaces and NBSP' => ["900101\u{00A0}1234 567", '9001011234567'];
        yield 'ideographic space' => ["900101\u{3000}1234567", '9001011234567'];
        yield 'fullwidth digits' => [
            "\u{FF19}\u{FF10}\u{FF10}\u{FF11}\u{FF10}\u{FF11}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}",
            '9001011234567',
        ];
        yield 'Arabic-Indic digits' => ["\u{0669}\u{0660}\u{0660}\u{0661}\u{0660}\u{0661}\u{0661}\u{0662}\u{0663}\u{0664}\u{0665}\u{0666}\u{0667}", '9001011234567'];
        yield 'issued after 2020-10, random digits, no check digit' => ['2112153000000', '2112153000000'];
        yield 'issued after 2020-10, all random digits equal' => ['2112154999999', '2112154999999'];
        yield 'old number with a wrong check digit is accepted' => ['9001011234560', '9001011234560'];
        yield '29 February 2004' => ['0402293123456', '0402293123456'];
        yield '29 February 2000' => ['0002293123456', '0002293123456'];
        yield 'born in the 1800s, man' => ['0101019123456', '0101019123456'];
        yield 'born in the 1800s, woman' => ['0101010123456', '0101010123456'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = ResidentRegistrationNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string, Sex, string}>
     */
    public static function decoded(): iterable
    {
        yield '1: man, 1900s' => ['9001011234567', Sex::Male, '1990-01-01'];
        yield '2: woman, 1900s' => ['9001012234567', Sex::Female, '1990-01-01'];
        yield '3: man, 2000s' => ['0002293123456', Sex::Male, '2000-02-29'];
        yield '4: woman, 2000s' => ['2112154000000', Sex::Female, '2021-12-15'];
        yield '9: man, 1800s' => ['8012319123456', Sex::Male, '1880-12-31'];
        yield '0: woman, 1800s' => ['8012310123456', Sex::Female, '1880-12-31'];
    }

    #[DataProvider('decoded')]
    public function testDecodesSexAndCenturyFromTheSeventhDigit(string $input, Sex $sex, string $date): void
    {
        $number = ResidentRegistrationNumber::from($input);

        self::assertSame($sex, $number->sex());
        self::assertSame($date . ' 00:00:00', $number->birthDate()->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $number->birthDate()->getTimezone()->getName());
        self::assertEquals(new DateTimeZone('UTC'), $number->birthDate()->getTimezone());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*********4567', ResidentRegistrationNumber::from('900101-1234567')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid Korean resident registration number';

        yield 'empty' => ['', $name . ' (0 characters).'];
        yield '12 digits' => ['900101123456', $name . ' (12 characters).'];
        yield '14 digits' => ['90010112345678', $name . ' (14 characters).'];
        yield 'far too long' => [\str_repeat('1', 41), $name . ' (too long).'];
        yield 'a letter' => ['90010112345A7', $name . ' (invalid character).'];
        yield 'punctuation' => ['900101.123456', $name . ' (invalid character).'];
        yield 'foreigner digit 5' => ['9001015234567', $name . ' (invalid character).'];
        yield 'foreigner digit 8' => ['9001018234567', $name . ' (invalid character).'];
        yield '29 February 2001' => ['0102293123456', $name . ' (invalid birth date).'];
        yield '29 February 1990' => ['9002291123456', $name . ' (invalid birth date).'];
        yield 'month 13' => ['9013011234567', $name . ' (invalid birth date).'];
        yield 'month 00' => ['9000011234567', $name . ' (invalid birth date).'];
        yield 'day 00' => ['9001001234567', $name . ' (invalid birth date).'];
        yield '30 February' => ['9002301234567', $name . ' (invalid birth date).'];
        yield '29 February 1900 (not a leap year)' => ['0002291234567', $name . ' (invalid birth date).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): ResidentRegistrationNumber => ResidentRegistrationNumber::from($value), $input, $message);
    }
}
