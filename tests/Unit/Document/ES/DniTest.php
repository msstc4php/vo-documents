<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\ES;

use MSSTC4PHP\ValueObject\Documents\Document\ES\ControlLetter;
use MSSTC4PHP\ValueObject\Documents\Document\ES\Dni;
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
 * Expectations follow docs/sources/ES.md "DNI". `12345678Z` is the Interior ministry's example; the other letters come
 * from {@see self::withLetter()}, an independent copy of the published table.
 */
#[CoversClass(Dni::class)]
#[CoversClass(ControlLetter::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class DniTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'official example' => ['12345678Z', '12345678Z'];
        yield 'lower-case letter' => ['12345678z', '12345678Z'];
        yield 'spaces, hyphen, NBSP' => ["12 345\u{00A0}678-Z", '12345678Z'];
        yield 'leading zero' => ['01234567L', '01234567L'];
        yield 'remainder 0' => ['00000023T', '00000023T'];
        yield 'remainder 22' => [self::withLetter('00000022'), '00000022E'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $number = Dni::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testEveryRemainderGivesItsLetter(): void
    {
        foreach (\range(0, 22) as $remainder) {
            $digits = \sprintf('%08d', 76_543_200 + $remainder);
            self::assertSame(self::withLetter($digits), Dni::from(self::withLetter($digits))->value);
        }
    }

    public function testMasks(): void
    {
        self::assertSame('*****678Z', Dni::from('12345678Z')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid Spanish DNI number (invalid character).';
        $checksum = 'Invalid Spanish DNI number checksum.';

        yield 'seven digits and a letter' => ['1234567L', 'Invalid Spanish DNI number (8 characters).'];
        yield 'ten characters' => ['123456789Z', 'Invalid Spanish DNI number (10 characters).'];
        yield 'digit in last place' => ['123456789', $invalid];
        yield 'letter among the digits' => ['1234A678Z', $invalid];
        yield 'NIE initial' => ['X1234567L', $invalid];
        yield 'exponent letter among the digits' => ['1E345678Z', $invalid];
        yield 'accented letter' => ['12345678É', $invalid];
        yield 'wrong letter' => ['12345678A', $checksum];
        yield 'letter outside the table' => ['12345678I', $checksum];
        yield 'transposed digits' => ['21345678Z', $checksum];
        yield 'empty' => ['', 'Invalid Spanish DNI number (0 characters).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid Spanish DNI number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Dni => Dni::from($value), $input, $message);
    }

    private static function withLetter(string $digits): string
    {
        return $digits . 'TRWAGMYFPDXBNJZSQVHLCKE'[(int) $digits % 23];
    }
}
