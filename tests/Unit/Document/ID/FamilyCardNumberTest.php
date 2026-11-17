<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\ID;

use MSSTC4PHP\ValueObject\Documents\Document\ID\FamilyCardNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/ID.md "Family card number": Dukcapil, "nomor KK terdiri dari 16 digit angka".
 */
#[CoversClass(FamilyCardNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class FamilyCardNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'plain' => ['3171010101230001', '3171010101230001'];
        yield 'spaces' => ['3171 0101 0123 0001', '3171010101230001'];
        yield 'hyphens' => ['317101-010123-0001', '3171010101230001'];
        yield 'NBSP' => ["317101\u{00A0}010123\u{00A0}0001", '3171010101230001'];
        yield 'fullwidth digits' => [
            "\u{FF13}\u{FF11}\u{FF17}\u{FF11}\u{FF10}\u{FF11}\u{FF10}\u{FF11}\u{FF10}\u{FF11}\u{FF12}\u{FF13}" .
                "\u{FF10}\u{FF10}\u{FF10}\u{FF11}",
            '3171010101230001',
        ];
        yield 'no date rule is applied (not quoted by Dukcapil)' => ['3171019999990001', '3171019999990001'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStores(string $input, string $expected): void
    {
        $number = FamilyCardNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('************0001', FamilyCardNumber::from('3171010101230001')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'empty' => ['', 'Invalid Indonesian family card number (0 characters).'];
        yield '15 digits' => ['317101010123000', 'Invalid Indonesian family card number (15 characters).'];
        yield '17 digits' => ['31710101012300011', 'Invalid Indonesian family card number (17 characters).'];
        yield 'far too long' => [\str_repeat('1', 41), 'Invalid Indonesian family card number (too long).'];
        yield 'letter' => ['317101010123000X', 'Invalid Indonesian family card number (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): FamilyCardNumber => FamilyCardNumber::from($value),
            $input,
            $message,
        );
    }
}
