<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\JP;

use MSSTC4PHP\ValueObject\Documents\Document\JP\BasicPensionNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/JP.md "Basic Pension Number": ten digits, a 4-digit and a 6-digit part, no
 * published check rule and no published example.
 */
#[CoversClass(BasicPensionNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class BasicPensionNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'digits only' => ['1234567890', '1234567890'];
        yield 'four and six' => ['1234-567890', '1234567890'];
        yield 'spaces and NBSP' => ["1234 567890\u{00A0}", '1234567890'];
        yield 'ideographic space' => ["1234\u{3000}567890", '1234567890'];
        yield 'leading zeros' => ['0000000001', '0000000001'];
        yield 'fullwidth digits' => [
            "\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF19}\u{FF10}",
            '1234567890',
        ];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = BasicPensionNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('******7890', BasicPensionNumber::from('1234567890')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'nine digits' => ['123456789', 'Invalid Basic Pension Number (wrong length).'];
        yield 'eleven digits' => ['12345678901', 'Invalid Basic Pension Number (wrong length).'];
        yield 'letter' => ['123456789A', 'Invalid Basic Pension Number (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): BasicPensionNumber => BasicPensionNumber::from($value),
            $input,
            $message,
        );
    }
}
