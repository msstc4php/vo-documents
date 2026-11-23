<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\KR;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Contract\HasBirthDate;
use MSSTC4PHP\ValueObject\Documents\Document\KR\ForeignerRegistrationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\KoreanRegistrationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\TamperedValue;
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
#[UsesClass(TwoDigitYearDate::class)]
final class ForeignerRegistrationNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;
    use TamperedValue;

    /**
     * @return iterable<string, array{string, Sex, string, int, int}>
     */
    public static function decoded(): iterable
    {
        yield '5: man' => ['8505055123456', Sex::Male, '85', 5, 5];
        yield '6: woman' => ['8505066123456', Sex::Female, '85', 5, 6];
        yield '7: man' => ['0502287123456', Sex::Male, '05', 2, 28];
        yield '8: woman' => ['0002298123456', Sex::Female, '00', 2, 29];
        yield '5 with 29 February 00, a date of 2000' => ['0002295123456', Sex::Male, '00', 2, 29];
        yield '8 with a 31 December' => ['9912318123456', Sex::Female, '99', 12, 31];
        yield '5 with 1 January' => ['8501015123456', Sex::Male, '85', 1, 1];
        yield '5 with 29 February 85 (ranges only, no calendar)' => ['8502295123456', Sex::Male, '85', 2, 29];
        yield '7 with 30 February' => ['0002307123456', Sex::Male, '00', 2, 30];
        yield '6 with 31 April' => ['8504316123456', Sex::Female, '85', 4, 31];
    }

    #[DataProvider('decoded')]
    public function testDecodesTheSexAndTheBirthDateDigits(
        string $input,
        Sex $sex,
        string $yearDigits,
        int $month,
        int $day,
    ): void {
        $number = ForeignerRegistrationNumber::from($input);

        self::assertSame($input, $number->value);
        self::assertSame($input, (string) $number);
        self::assertSame($input, $number->jsonSerialize());
        self::assertSame($sex, $number->sex());
        self::assertSame($yearDigits, $number->birthYearDigits());
        self::assertSame($month, $number->birthMonth());
        self::assertSame($day, $number->birthDay());
    }

    public function testHasNoBirthDateBecauseNoOfficialTextGivesTheCentury(): void
    {
        self::assertNotContains(HasBirthDate::class, \class_implements(ForeignerRegistrationNumber::class));
    }

    public function testSeparatorsAndFullwidthDigitsAreFolded(): void
    {
        self::assertSame('8505055123456', ForeignerRegistrationNumber::from('850505-5123456')->value);
        self::assertSame('8505055123456', ForeignerRegistrationNumber::from("850505\u{00A0}5123456")->value);
        self::assertSame(
            '8505055123456',
            ForeignerRegistrationNumber::from(
                "\u{FF18}\u{FF15}\u{FF10}\u{FF15}\u{FF10}\u{FF15}\u{FF15}\u{FF11}\u{FF12}" .
                "\u{FF13}\u{FF14}\u{FF15}\u{FF16}",
            )->value,
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

        yield 'empty' => ['', $name . ' (wrong length).'];
        yield '12 digits' => ['850505512345', $name . ' (wrong length).'];
        yield '14 digits' => ['85050551234567', $name . ' (wrong length).'];
        yield 'a letter' => ['85050551234X6', $name . ' (invalid character).'];
        yield 'resident digit 1' => ['8505051123456', $name . ' (invalid character).'];
        yield 'resident digit 4' => ['8505054123456', $name . ' (invalid character).'];
        yield 'digit 9' => ['8505059123456', $name . ' (invalid character).'];
        yield 'digit 0' => ['8505050123456', $name . ' (invalid character).'];
        yield 'month 13' => ['8513055123456', $name . ' (invalid birth date).'];
        yield 'day 32' => ['8501325123456', $name . ' (invalid birth date).'];
        yield 'day 0' => ['8505005123456', $name . ' (invalid birth date).'];
        yield 'month 0' => ['8500055123456', $name . ' (invalid birth date).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): ForeignerRegistrationNumber => ForeignerRegistrationNumber::from($value),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{string, 'birthDay'|'birthMonth'|'sex'}>
     */
    public static function tamperedValues(): iterable
    {
        yield 'letter as the seventh digit, sex' => ['900101X234567', 'sex'];
        yield 'too short, sex' => ['900101', 'sex'];
        yield 'month 13' => ['9013015234567', 'birthMonth'];
        yield 'month 0' => ['9000015234567', 'birthMonth'];
        yield 'day 32' => ['9001325234567', 'birthDay'];
        yield 'day 0' => ['9001005234567', 'birthDay'];
    }

    /**
     * @param 'birthDay'|'birthMonth'|'sex' $accessor
     */
    #[DataProvider('tamperedValues')]
    public function testATamperedValueFailsWithFixedText(string $value, string $accessor): void
    {
        $number = self::tampered(ForeignerRegistrationNumber::class, $value);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Korean registration number validated by normalize().');

        match ($accessor) {
            'sex' => $number->sex(),
            'birthDay' => $number->birthDay(),
            'birthMonth' => $number->birthMonth(),
        };
    }
}
