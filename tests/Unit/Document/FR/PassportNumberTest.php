<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Document\FR\PassportNumber;
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
 * Expectations follow docs/sources/FR.md "Passport number".
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
        yield 'two digits, two letters, five digits' => ['12AB34567', '12AB34567'];
        yield 'lower case' => ['09zz00001', '09ZZ00001'];
        yield 'spaces, hyphen, NBSP' => ["12 AB-34\u{00A0}567", '12AB34567'];
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
        self::assertSame('*****4567', PassportNumber::from('12AB34567')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid French passport number (invalid character).';

        yield 'eight characters' => ['12AB3456', 'Invalid French passport number (8 characters).'];
        yield 'ten characters (with the MRZ check digit)' => [
            '12AB345671',
            'Invalid French passport number (10 characters).',
        ];
        yield 'letter in the first pair' => ['1AAB34567', $invalid];
        yield 'letter first' => ['A2AB34567', $invalid];
        yield 'digit in the letter pair' => ['12A134567', $invalid];
        yield 'digit first in the letter pair' => ['121B34567', $invalid];
        yield 'digits only' => ['123456789', $invalid];
        yield 'letter in the last five' => ['12AB3456C', $invalid];
        yield 'letter fifth' => ['12ABC4567', $invalid];
        yield 'accented letter' => ['12ÉB34567', $invalid];
        yield 'empty' => ['', 'Invalid French passport number (0 characters).'];
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
