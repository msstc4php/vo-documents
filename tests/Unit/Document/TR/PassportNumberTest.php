<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\TR;

use MSSTC4PHP\ValueObject\Documents\Document\TR\PassportNumber;
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
 * Expectations follow docs/sources/TR.md "Passport number"; the examples are the PRADO specimens.
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(NumberFormat::class)]
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
        yield '2010 specimen as printed' => ['U 00000261', 'U00000261'];
        yield '2018 specimen' => ['U20041774', 'U20041774'];
        yield 'lower-case letter' => ['u20041774', 'U20041774'];
        yield 'another letter' => ['S12345678', 'S12345678'];
        yield 'NBSP' => ["U\u{00A0}20041774", 'U20041774'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFourCharacters(): void
    {
        self::assertSame('*****0261', PassportNumber::from('U00000261')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'seven digits' => ['U1234567', 'Invalid Turkish passport number (8 characters).'];
        yield 'nine digits' => ['U123456789', 'Invalid Turkish passport number (10 characters).'];
        yield 'no letter' => ['123456789', 'Invalid Turkish passport number (invalid character).'];
        yield 'two letters' => ['UA2345678', 'Invalid Turkish passport number (invalid character).'];
        yield 'letter at the end' => ['12345678U', 'Invalid Turkish passport number (invalid character).'];
        yield 'dotted capital I' => ["\u{0130}12345678", 'Invalid Turkish passport number (invalid character).'];
        yield 'sign' => ['U1234567#', 'Invalid Turkish passport number (invalid character).'];
        yield 'empty' => ['', 'Invalid Turkish passport number (0 characters).'];
        yield 'far too long' => ['U' . \str_repeat('1', 50), 'Invalid Turkish passport number (too long).'];
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
