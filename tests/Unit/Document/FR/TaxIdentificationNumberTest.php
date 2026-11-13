<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Document\FR\TaxIdentificationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/FR.md "Numéro fiscal".
 */
#[CoversClass(TaxIdentificationNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class TaxIdentificationNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'published layout (synthetic number)' => ['30 23 217 600 053', '3023217600053'];
        yield 'compact, first digit 0' => ['0123456789012', '0123456789012'];
        yield 'first digit 1' => ['1123456789012', '1123456789012'];
        yield 'first digit 2, NBSP' => ["2123\u{00A0}456789012", '2123456789012'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheDigits(string $input, string $expected): void
    {
        $number = TaxIdentificationNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $number = TaxIdentificationNumber::from('3023217600053');

        self::assertSame('30 23 217 600 053', $number->formatted());
        self::assertSame('** ** *** **0 053', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $firstDigit = 'Invalid French tax identification number (invalid first digit).';

        yield 'first digit 4' => ['4023217600053', $firstDigit];
        yield 'first digit 9' => ['9023217600053', $firstDigit];
        yield 'twelve digits' => ['302321760005', 'Invalid French tax identification number (12 characters).'];
        yield 'fourteen digits' => ['30232176000531', 'Invalid French tax identification number (14 characters).'];
        yield 'letter' => ['30232176000A3', 'Invalid French tax identification number (invalid character).'];
        yield 'empty' => ['', 'Invalid French tax identification number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): TaxIdentificationNumber => TaxIdentificationNumber::from($value),
            $input,
            $message,
        );
    }
}
