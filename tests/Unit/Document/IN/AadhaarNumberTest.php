<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\IN;

use MSSTC4PHP\ValueObject\Documents\Document\IN\AadhaarNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "Aadhaar number": 12 digits, Verhoeff check digit, UIDAI's
 * masked form.
 */
#[CoversClass(AadhaarNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
final class AadhaarNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'plain' => ['234567890124', '234567890124'];
        yield 'groups of four' => ['2345 6789 0124', '234567890124'];
        yield 'hyphens' => ['2345-6789-0124', '234567890124'];
        yield 'NBSP and tabs' => ["2345\u{00A0}6789\t0124", '234567890124'];
        yield 'fullwidth digits' => [
            "\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF19}\u{FF10}\u{FF11}\u{FF12}\u{FF14}",
            '234567890124',
        ];
        yield 'Arabic-Indic digits' => [
            "\u{0662}\u{0663}\u{0664}\u{0665}\u{0666}\u{0667}\u{0668}\u{0669}\u{0660}\u{0661}\u{0662}\u{0664}",
            '234567890124',
        ];
        yield 'first digit 2' => ['200000000009', '200000000009'];
        yield 'first digit 9' => ['999999999999', '999999999999'];
        yield 'first digit 0 is not refused' => ['000000000003', '000000000003'];
        yield 'first digit 1 is not refused' => ['123456789010', '123456789010'];
        yield 'first digit 7' => ['700000000016', '700000000016'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = AadhaarNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMaskedShowsTheLastFourDigitsAsUidaiDoes(): void
    {
        self::assertSame('XXXX XXXX 0124', AadhaarNumber::from('234567890124')->masked());
        self::assertSame('XXXX XXXX 9999', AadhaarNumber::from('9999 9999 9999')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid Aadhaar number';

        yield 'empty' => ['', $name . ' (wrong length).'];
        yield 'eleven digits' => ['23456789012', $name . ' (wrong length).'];
        yield 'thirteen digits' => ['2345678901240', $name . ' (wrong length).'];
        yield 'virtual ID length' => ['2345678901234567', $name . ' (wrong length).'];
        yield 'far too long' => [\str_repeat('2', 41), $name . ' (wrong length).'];
        yield 'letter' => ['23456789012A', $name . ' (invalid character).'];
        yield 'punctuation' => ['23456789012.', $name . ' (invalid character).'];
        yield 'Devanagari digits are not folded' => [
            "\u{0968}\u{0969}\u{096A}\u{096B}\u{096C}\u{096D}\u{096E}\u{096F}\u{0966}\u{0967}\u{0968}\u{096A}",
            $name . ' (invalid character).',
        ];
        yield 'wrong check digit' => ['234567890125', $name . ' checksum.'];
        yield 'adjacent transposition' => ['234567890214', $name . ' checksum.'];
        yield 'single digit error' => ['234567890134', $name . ' checksum.'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AadhaarNumber => AadhaarNumber::from($value),
            $input,
            $message,
        );
    }

    public function testEqualityIgnoresTheFormattingOfTheInput(): void
    {
        self::assertTrue(AadhaarNumber::from('2345 6789 0124')->equals(AadhaarNumber::from('234567890124')));
        self::assertFalse(AadhaarNumber::from('999999999999')->equals(AadhaarNumber::from('234567890124')));
    }
}
