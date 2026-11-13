<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\IT;

use MSSTC4PHP\ValueObject\Documents\Document\IT\PassportNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IT.md "Passport number". The decrees give no example number; the tests use numbers
 * of the stated shape.
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PassportNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'two letters, seven digits' => ['YA1234567', 'YA1234567'];
        yield 'lower case' => ['ya0000001', 'YA0000001'];
        yield 'spaces, hyphen, NBSP' => ["YA 123-45\u{00A0}67", 'YA1234567'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasks(): void
    {
        self::assertSame('*****4567', PassportNumber::from('YA1234567')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid Italian passport number (invalid character).';

        yield 'eight characters' => ['YA123456', 'Invalid Italian passport number (8 characters).'];
        yield 'ten characters (with the MRZ check digit)' => [
            'YA12345671',
            'Invalid Italian passport number (10 characters).',
        ];
        yield 'one letter' => ['Y12345678', $invalid];
        yield 'digit first' => ['1A1234567', $invalid];
        yield 'three letters' => ['YAB234567', $invalid];
        yield 'letter last' => ['YA123456B', $invalid];
        yield 'digits only' => ['123456789', $invalid];
        yield 'accented letter' => ['YÀ1234567', $invalid];
        yield 'empty' => ['', 'Invalid Italian passport number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): PassportNumber => PassportNumber::from($value),
            $input,
            $message,
        );
    }
}
