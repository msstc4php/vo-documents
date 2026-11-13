<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\ES;

use MSSTC4PHP\ValueObject\Documents\Document\ES\ControlLetter;
use MSSTC4PHP\ValueObject\Documents\Document\ES\Nie;
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
 * Expectations follow docs/sources/ES.md "NIE". No official example is published; the letters were computed with the
 * Interior ministry's rule (X → 0, Y → 1, Z → 2, then the DNI table). Pre-2008 numbers are format only.
 */
#[CoversClass(Nie::class)]
#[UsesClass(ControlLetter::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class NieTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'X' => ['X1234567L', 'X1234567L'];
        yield 'Y' => ['Y1234567X', 'Y1234567X'];
        yield 'Z' => ['Z1234567R', 'Z1234567R'];
        yield 'X and zeros' => ['X0000000T', 'X0000000T'];
        yield 'Y and nines' => ['Y9999999G', 'Y9999999G'];
        yield 'lower case' => ['x1234567l', 'X1234567L'];
        yield 'separators' => ["X-1234567\u{202F}L", 'X1234567L'];
        yield 'pre-2008, any letter (format only)' => ['X12345678Z', 'X12345678Z'];
        yield 'pre-2008, letter T of the 1997 figure' => ['x00000001t', 'X00000001T'];
        yield 'pre-2008, separators' => ['X 1234 5678-A', 'X12345678A'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $number = Nie::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasks(): void
    {
        self::assertSame('*****567L', Nie::from('X1234567L')->masked());
        self::assertSame('******678Z', Nie::from('X12345678Z')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid Spanish NIE (invalid character).';
        $checksum = 'Invalid Spanish NIE checksum.';

        yield 'eleven characters' => ['X123456789Z', 'Invalid Spanish NIE (11 characters).'];
        yield 'pre-2008 layout with Y' => ['Y12345678Z', $invalid];
        yield 'pre-2008 layout, digit last' => ['X123456789', $invalid];
        yield 'pre-2008 layout, letter among the digits' => ['X1234A678Z', $invalid];
        yield 'pre-2008 layout, accented letter last' => ['X12345678É', $invalid];
        yield 'six digits' => ['X123456L', 'Invalid Spanish NIE (8 characters).'];
        yield 'initial after Z' => ['W1234567L', $invalid];
        yield 'NIF letter M' => ['M1234567L', $invalid];
        yield 'DNI number' => ['12345678Z', $invalid];
        yield 'digit in last place' => ['X12345670', $invalid];
        yield 'letter among the digits' => ['X12A4567L', $invalid];
        yield 'two initials' => ['XY234567L', $invalid];
        yield 'letter of another initial' => ['Y1234567L', $checksum];
        yield 'wrong letter' => ['X1234567T', $checksum];
        yield 'empty' => ['', 'Invalid Spanish NIE (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Nie => Nie::from($value), $input, $message);
    }
}
