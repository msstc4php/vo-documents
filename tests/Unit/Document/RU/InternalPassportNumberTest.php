<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\InternalPassportNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "Internal passport". The source has no check digit and no example number, so the
 * value is checked by its shape only.
 */
#[CoversClass(InternalPassportNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class InternalPassportNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'formatted' => ['45 08 123456', '4508123456'];
        yield 'digits only' => ['4508123456', '4508123456'];
        yield 'series and number' => ['4508 123456', '4508123456'];
        yield 'tab and NBSP' => ["45\t08\u{00A0}123456", '4508123456'];
        yield 'hyphens' => ['45-08-123456', '4508123456'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = InternalPassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $number = InternalPassportNumber::from('4508123456');

        self::assertSame('4508', $number->series());
        self::assertSame('123456', $number->number());

        self::assertSame('45 08 123456', $number->formatted());
        self::assertSame('** ** **3456', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'too short' => ['450812345', 'Invalid Russian internal passport number (9 characters).'];
        yield 'too long' => ['45081234567', 'Invalid Russian internal passport number (11 characters).'];
        yield 'letters' => ['45O8123456', 'Invalid Russian internal passport number (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): InternalPassportNumber => InternalPassportNumber::from($value),
            $input,
            $message,
        );
    }
}
