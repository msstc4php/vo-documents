<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\KR;

use MSSTC4PHP\ValueObject\Documents\Document\KR\OrdinaryPassportNumber;
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
 * Expectations follow docs/sources/KR.md "Ordinary passport number": MOFA's `M12345678` and `M123A4567`.
 */
#[CoversClass(OrdinaryPassportNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class OrdinaryPassportNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'MOFA old form M12345678' => ['M12345678', 'M12345678'];
        yield 'MOFA 2021 form M123A4567' => ['M123A4567', 'M123A4567'];
        yield 'another letter in the fifth place' => ['M123Z4567', 'M123Z4567'];
        yield 'lower case, separators, NBSP' => ["m123 a-4567\u{00A0}", 'M123A4567'];
        yield 'fullwidth' => ["\u{FF2D}\u{FF11}\u{FF12}\u{FF13}\u{FF21}\u{FF14}\u{FF15}\u{FF16}\u{FF17}", 'M123A4567'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = OrdinaryPassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*****4567', OrdinaryPassportNumber::from('M123A4567')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid Korean ordinary passport number';

        yield 'letter in the wrong place' => ['M12A34567', $name . ' (invalid character).'];
        yield 'two letters' => ['M12AB4567', $name . ' (invalid character).'];
        yield 'letter in the last place' => ['M123A456A', $name . ' (invalid character).'];
        yield 'digit first' => ['112345678', $name . ' (invalid character).'];
        yield 'another series letter' => ['G12345678', $name . ' (invalid character).'];
        yield 'D series' => ['D123A4567', $name . ' (invalid character).'];
        yield 'punctuation' => ['M1234567.', $name . ' (invalid character).'];
        yield 'eight characters' => ['M1234567', $name . ' (8 characters).'];
        yield 'ten characters' => ['M123A45678', $name . ' (10 characters).'];
        yield 'far too long' => [\str_repeat('M', 41), $name . ' (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): OrdinaryPassportNumber => OrdinaryPassportNumber::from($value), $input, $message);
    }
}
