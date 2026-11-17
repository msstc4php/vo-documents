<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\KR;

use MSSTC4PHP\ValueObject\Documents\Document\KR\ForeignerRegistrationNumber;
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
 * Expectations follow docs/sources/KR.md "Foreigner registration number".
 */
#[CoversClass(ForeignerRegistrationNumber::class)]
#[CoversClass(KoreanRegistrationNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class ForeignerRegistrationNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, Sex, string}>
     */
    public static function decoded(): iterable
    {
        yield '5: man, 1900s' => ['8505055123456', Sex::Male, '1985-05-05'];
        yield '6: woman, 1900s' => ['8505056123456', Sex::Female, '1985-05-05'];
        yield '7: man, 2000s' => ['0502287123456', Sex::Male, '2005-02-28'];
        yield '8: woman, 2000s' => ['0002298123456', Sex::Female, '2000-02-29'];
    }

    #[DataProvider('decoded')]
    public function testDecodesSexAndCenturyFromTheSeventhDigit(string $input, Sex $sex, string $date): void
    {
        $number = ForeignerRegistrationNumber::from($input);

        self::assertSame($input, $number->value);
        self::assertSame($input, (string) $number);
        self::assertSame($input, $number->jsonSerialize());
        self::assertSame($sex, $number->sex());
        self::assertSame($date . ' 00:00:00', $number->birthDate()->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $number->birthDate()->getTimezone()->getName());
    }

    public function testSeparatorsAndFullwidthDigitsAreFolded(): void
    {
        self::assertSame('8505055123456', ForeignerRegistrationNumber::from('850505-5123456')->value);
        self::assertSame('8505055123456', ForeignerRegistrationNumber::from("850505\u{00A0}5123456")->value);
        self::assertSame(
            '8505055123456',
            ForeignerRegistrationNumber::from("\u{FF18}\u{FF15}\u{FF10}\u{FF15}\u{FF10}\u{FF15}\u{FF15}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}")->value,
        );
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*********3456', ForeignerRegistrationNumber::from('850505-5123456')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid Korean foreigner registration number';

        yield 'empty' => ['', $name . ' (0 characters).'];
        yield '12 digits' => ['850505512345', $name . ' (12 characters).'];
        yield '14 digits' => ['85050551234567', $name . ' (14 characters).'];
        yield 'a letter' => ['85050551234X6', $name . ' (invalid character).'];
        yield 'resident digit 1' => ['8505051123456', $name . ' (invalid character).'];
        yield 'resident digit 4' => ['8505054123456', $name . ' (invalid character).'];
        yield 'digit 9' => ['8505059123456', $name . ' (invalid character).'];
        yield 'digit 0' => ['8505050123456', $name . ' (invalid character).'];
        yield 'month 13' => ['8513055123456', $name . ' (invalid birth date).'];
        yield '29 February 1985' => ['8502295123456', $name . ' (invalid birth date).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): ForeignerRegistrationNumber => ForeignerRegistrationNumber::from($value), $input, $message);
    }
}
