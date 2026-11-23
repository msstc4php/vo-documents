<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CN;

use DateTimeZone;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Document\CN\CitizenIdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Gb11643;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\TamperedValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CN.md "Citizen identity number": GB 11643-1999 §5 and Annex A, the NIA's example of
 * 2023 and the Hong Kong, Macao and Taiwan resident permit's address codes.
 */
#[CoversClass(CitizenIdentityNumber::class)]
#[CoversClass(Gb11643::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
#[UsesClass(Checksum::class)]
final class CitizenIdentityNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;
    use TamperedValue;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'GB 11643 A1, check character X' => ['11010519491231002X', '11010519491231002X'];
        yield 'GB 11643 A2, born in 1880' => ['440524188001010014', '440524188001010014'];
        yield 'lower-case x is stored as X' => ['11010519491231002x', '11010519491231002X'];
        yield 'spaces, hyphens, NBSP' => ["110105 1949-1231\u{00A0}002X", '11010519491231002X'];
        yield 'ideographic space' => ["110105\u{3000}19491231002X", '11010519491231002X'];
        yield 'fullwidth digits and letter' => [
            "\u{FF11}\u{FF11}\u{FF10}\u{FF11}\u{FF10}\u{FF15}\u{FF11}\u{FF19}\u{FF14}\u{FF19}\u{FF11}\u{FF12}" .
                "\u{FF13}\u{FF11}\u{FF10}\u{FF10}\u{FF12}\u{FF38}",
            '11010519491231002X',
        ];
        yield 'fullwidth lower-case x' => [
            "\u{FF11}\u{FF11}\u{FF10}\u{FF11}\u{FF10}\u{FF15}\u{FF11}\u{FF19}\u{FF14}\u{FF19}\u{FF11}\u{FF12}" .
                "\u{FF13}\u{FF11}\u{FF10}\u{FF10}\u{FF12}\u{FF58}",
            '11010519491231002X',
        ];
        yield 'Hong Kong resident permit' => ['810000199001011230', '810000199001011230'];
        yield 'Macao resident permit' => ['820000199001011240', '820000199001011240'];
        yield 'Taiwan resident permit' => ['830000199001011250', '830000199001011250'];
        yield '29 February 2000' => ['110105200002290013', '110105200002290013'];
        yield '29 February 1880' => ['110105188002290014', '110105188002290014'];
        yield 'year 9999 (no rule bounds the year)' => ['110105999901010011', '110105999901010011'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $number = CitizenIdentityNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string, string, Sex, string}>
     */
    public static function fields(): iterable
    {
        yield 'GB 11643 A1: woman born 1949-12-31, Beijing Chaoyang' => [
            '11010519491231002X',
            '1949-12-31',
            Sex::Female,
            '110105',
        ];
        yield 'GB 11643 A2: man born 1880-01-01, Guangdong Chaoyang' => [
            '440524188001010014',
            '1880-01-01',
            Sex::Male,
            '440524',
        ];
        yield 'sequence 123: odd, a man; Hong Kong' => ['810000199001011230', '1990-01-01', Sex::Male, '810000'];
        yield 'sequence 124: even, a woman; Macao' => ['820000199001011240', '1990-01-01', Sex::Female, '820000'];
        yield 'sequence 125: odd; Taiwan' => ['830000199001011250', '1990-01-01', Sex::Male, '830000'];
        yield 'sequence 003: odd' => ['110105199001010037', '1990-01-01', Sex::Male, '110105'];
        yield 'sequence 004: even' => ['110105199001010045', '1990-01-01', Sex::Female, '110105'];
    }

    #[DataProvider('fields')]
    public function testReadsTheFields(string $input, string $date, Sex $sex, string $division): void
    {
        $number = CitizenIdentityNumber::from($input);

        self::assertSame($date . ' 00:00:00', $number->birthDate()->format('Y-m-d H:i:s'));
        self::assertEquals(new DateTimeZone('UTC'), $number->birthDate()->getTimezone());
        self::assertSame($sex, $number->sex());
        self::assertSame($division, $number->administrativeDivisionCode());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('**************002X', CitizenIdentityNumber::from('11010519491231002X')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Chinese citizen identity number (invalid character).';
        $date = 'Invalid Chinese citizen identity number (invalid birth date).';
        $checksum = 'Invalid Chinese citizen identity number checksum.';

        yield 'first-generation number of 15 digits (invalid since 2013-01-01)' => [
            '110105491231002',
            'Invalid Chinese citizen identity number (wrong length).',
        ];
        yield 'too short' => ['11010519491231002', 'Invalid Chinese citizen identity number (wrong length).'];
        yield 'too long' => ['11010519491231002X0', 'Invalid Chinese citizen identity number (wrong length).'];
        yield 'far too long' => [\str_repeat('1', 41), 'Invalid Chinese citizen identity number (wrong length).'];
        yield 'empty' => ['', 'Invalid Chinese citizen identity number (wrong length).'];
        yield 'X inside the number' => ['1101051949123X0020', $character];
        yield 'X first' => ['X1010519491231002X', $character];
        yield 'another letter as the check character' => ['11010519491231002A', $character];
        yield 'punctuation' => ['11010519491231002.', $character];
        yield 'checksum off by one' => ['110105194912310021', $checksum];
        yield 'checksum X for a digit' => ['44052418800101001X', $checksum];
        yield 'digit for X' => ['110105194912310020', $checksum];
        yield 'transposed digits' => ['11010519491213002X', $checksum];
        yield '29 February 1900 is not a date' => ['110105190002290017', $date];
        yield '29 February 2001' => ['110105200102290010', $date];
        yield 'month 13' => ['110105199913010013', $date];
        yield 'month 00' => ['110105199900010014', $date];
        yield 'day 00' => ['110105199901000010', $date];
        yield 'day 32' => ['110105199901320014', $date];
        yield '31 April' => ['110105199904310014', $date];
        yield 'year 0000' => ['11010500000101001X', $date];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): CitizenIdentityNumber => CitizenIdentityNumber::from($value),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function tamperedValues(): iterable
    {
        yield 'month 13' => ['110105199013071239'];
        yield 'day 00' => ['110105199003001239'];
        yield 'letter in the year that casts to a valid year' => ['110105199a03071239'];
        yield 'too short' => ['110105'];
    }

    #[DataProvider('tamperedValues')]
    public function testATamperedBirthDateFailsWithFixedText(string $value): void
    {
        $number = self::tampered(CitizenIdentityNumber::class, $value);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('GB 11643 number validated by assertBirthDate().');

        $number->birthDate();
    }
}
