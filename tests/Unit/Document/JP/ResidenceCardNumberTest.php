<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\JP;

use MSSTC4PHP\ValueObject\Documents\Document\JP\ResidenceCardNumber;
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
 * Expectations follow docs/sources/JP.md "Residence card number": "12桁（英字２桁ｰ数字８桁ｰ英字２桁）", no check rule
 * and no example published.
 */
#[CoversClass(ResidenceCardNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class ResidenceCardNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'two letters, eight digits, two letters' => ['AB12345678CD', 'AB12345678CD'];
        yield 'lower case' => ['ab12345678cd', 'AB12345678CD'];
        yield 'hyphens as printed' => ['AB-12345678-CD', 'AB12345678CD'];
        yield 'spaces and NBSP' => ["AB 12345678\u{00A0}CD", 'AB12345678CD'];
        yield 'fullwidth with fullwidth hyphens' => [
            "\u{FF21}\u{FF22}\u{FF0D}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}"
            . "\u{FF0D}\u{FF23}\u{FF24}",
            'AB12345678CD',
        ];
        yield 'halfwidth prolonged sound mark' => ["AB\u{FF70}12345678\u{FF70}CD", 'AB12345678CD'];
        yield 'fullwidth' => [
            "\u{FF21}\u{FF22}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF23}\u{FF24}",
            'AB12345678CD',
        ];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStores(string $input, string $expected): void
    {
        $number = ResidenceCardNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('********78CD', ResidenceCardNumber::from('AB12345678CD')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $document = 'Invalid Japanese residence card number';

        yield 'digits only' => ['123456789012', $document . ' (invalid character).'];
        yield 'letters only' => ['ABCDEFGHIJKL', $document . ' (invalid character).'];
        yield 'one trailing letter' => ['AB123456789C', $document . ' (invalid character).'];
        yield 'seven digits' => ['AB1234567CD', $document . ' (wrong length).'];
        yield 'nine digits' => ['AB123456789CD', $document . ' (wrong length).'];
        yield 'non-ASCII letter' => ["\u{00C4}B12345678CD", $document . ' (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): ResidenceCardNumber => ResidenceCardNumber::from($value),
            $input,
            $message,
        );
    }
}
