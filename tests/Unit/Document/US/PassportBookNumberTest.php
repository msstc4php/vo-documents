<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\PassportBookNumber;
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
 * Expectations follow docs/sources/US.md "Passport book number". No official example number is published, so the
 * numbers are synthetic.
 */
#[CoversClass(PassportBookNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PassportBookNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'next generation, letter and eight digits' => ['A12345678', 'A12345678'];
        yield 'lower case' => ['z12345678', 'Z12345678'];
        yield 'older, nine digits' => ['123456789', '123456789'];
        yield 'older, letter and seven digits' => ['X1234567', 'X1234567'];
        yield 'separators' => ["A 1234\u{00A0}5678", 'A12345678'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        self::assertSame($expected, PassportBookNumber::from($input)->value);
    }

    public function testMasksKeepingFourOrThreeCharacters(): void
    {
        self::assertSame('*****5678', PassportBookNumber::from('A12345678')->masked());
        self::assertSame('*****6789', PassportBookNumber::from('123456789')->masked());
        self::assertSame('*****567', PassportBookNumber::from('X1234567')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid American passport book number (invalid character).';

        yield 'eight digits' => ['12345678', $character];
        yield 'two letters' => ['AB2345678', $character];
        yield 'letter not first' => ['1A2345678', $character];
        yield 'letter last' => ['A1234567B', $character];
        yield 'non-ASCII digit' => ["A1234567\u{0663}", $character];
        yield 'seven characters' => ['A123456', 'Invalid American passport book number (7 characters).'];
        yield 'ten characters' => ['A123456789', 'Invalid American passport book number (10 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): PassportBookNumber => PassportBookNumber::from($value),
            $input,
            $message,
        );
    }
}
