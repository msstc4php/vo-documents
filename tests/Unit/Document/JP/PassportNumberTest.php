<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\JP;

use MSSTC4PHP\ValueObject\Documents\Document\JP\PassportNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/JP.md "Passport number": "英字2桁と数字7桁", MOFA's own sample AB1234567.
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(NumberShapes::class)]
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
        yield 'MOFA sample' => ['AB1234567', 'AB1234567'];
        yield 'lower case' => ['ab1234567', 'AB1234567'];
        yield 'separators' => ['AB 123-4567', 'AB1234567'];
        yield 'NBSP and ideographic space' => ["AB\u{00A0}123\u{3000}4567", 'AB1234567'];
        yield 'fullwidth' => ["\u{FF21}\u{FF22}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}", 'AB1234567'];
        yield 'other letters' => ['TZ0000000', 'TZ0000000'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStores(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*****4567', PassportNumber::from('AB1234567')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'three letters' => ['ABC123456', 'Invalid Japanese passport number (invalid character).'];
        yield 'one letter and eight digits' => ['A12345678', 'Invalid Japanese passport number (invalid character).'];
        yield 'letters last' => ['1234567AB', 'Invalid Japanese passport number (invalid character).'];
        yield 'too short' => ['AB123456', 'Invalid Japanese passport number (wrong length).'];
        yield 'too long' => ['AB12345678', 'Invalid Japanese passport number (wrong length).'];
        yield 'non-ASCII letter' => ["\u{00C4}B1234567", 'Invalid Japanese passport number (invalid character).'];
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
