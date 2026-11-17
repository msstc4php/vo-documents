<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\IN;

use MSSTC4PHP\ValueObject\Documents\Document\IN\AbhaNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "ABHA number": a 14-digit number, format only.
 */
#[CoversClass(AbhaNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class AbhaNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'plain' => ['91123456789012', '91123456789012'];
        yield 'hyphen groups 2-4-4-4' => ['91-1234-5678-9012', '91123456789012'];
        yield 'spaces and NBSP' => ["91 1234\u{00A0}5678 9012", '91123456789012'];
        yield 'fullwidth' => [
            "\u{FF19}\u{FF11}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF19}\u{FF10}" .
                "\u{FF11}\u{FF12}",
            '91123456789012',
        ];
        yield 'leading zero' => ['00123456789012', '00123456789012'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = AbhaNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('**********9012', AbhaNumber::from('91123456789012')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid ABHA number';

        yield 'thirteen digits' => ['9112345678901', $name . ' (13 characters).'];
        yield 'fifteen digits' => ['911234567890123', $name . ' (15 characters).'];
        yield 'twelve digits (an Aadhaar)' => ['234567890124', $name . ' (12 characters).'];
        yield 'far too long' => [\str_repeat('9', 41), $name . ' (too long).'];
        yield 'letter' => ['9112345678901A', $name . ' (invalid character).'];
        yield 'ABHA address' => ['name@abdm', $name . ' (9 characters).'];
        yield 'Devanagari digits' => [
            "\u{096F}\u{0967}\u{0967}\u{0968}\u{0969}\u{096A}\u{096B}\u{096C}\u{096D}\u{096E}\u{096F}\u{0966}" .
                "\u{0967}\u{0968}",
            $name . ' (invalid character).',
        ];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbhaNumber => AbhaNumber::from($value),
            $input,
            $message,
        );
    }
}
