<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CH;

use MSSTC4PHP\ValueObject\Documents\Document\CH\AhvNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CH.md "AHV number". `756.3047.5009.62` is the display example of WL VA/IK Rz 1201,
 * `756.1234.5678.97` the worked example of its Anhang 7.
 */
#[CoversClass(AhvNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class AhvNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'Rz 1201 display example' => ['756.3047.5009.62', '7563047500962'];
        yield 'Anhang 7 worked example' => ['756.1234.5678.97', '7561234567897'];
        yield 'check digit zero' => ['756.0000.0000.40', '7560000000040'];
        yield 'compact' => ['7563047500962', '7563047500962'];
        yield 'spaces and NBSP' => ["756 3047\u{00A0}5009 62", '7563047500962'];
        yield 'hyphens' => ['756-3047-5009-62', '7563047500962'];
        yield 'display form with spaces' => [' 756. 3047. 5009. 62 ', '7563047500962'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = AhvNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAsTheDisplayForm(): void
    {
        self::assertSame('756.3047.5009.62', AhvNumber::from('7563047500962')->formatted());
    }

    public function testMasksInTheDisplayLayout(): void
    {
        self::assertSame('***.****.**09.62', AhvNumber::from('7563047500962')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $checksum = 'Invalid Swiss AHV number checksum.';

        yield 'last digit changed' => ['756.3047.5009.63', $checksum];
        yield 'transposed digits' => ['756.3047.5090.62', $checksum];
        yield 'UPI handbook illustration row' => ['756.1234.5678.90', $checksum];
        yield 'another country code' => ['757.3047.5009.62', 'Invalid Swiss AHV number (country code).'];
        yield 'old eleven-digit number' => ['123.45.678.901', 'Invalid Swiss AHV number (11 characters).'];
        yield 'twelve digits' => ['756.3047.5009.6', 'Invalid Swiss AHV number (12 characters).'];
        yield 'fourteen digits' => ['756.3047.5009.621', 'Invalid Swiss AHV number (14 characters).'];
        yield 'letter' => ['756.3047.5009.6A', 'Invalid Swiss AHV number (invalid character).'];
        yield 'slash' => ['756/304750096', 'Invalid Swiss AHV number (invalid character).'];
        yield 'empty' => ['', 'Invalid Swiss AHV number (0 characters).'];
        yield 'far too long' => [\str_repeat('7', 50), 'Invalid Swiss AHV number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AhvNumber => AhvNumber::from($value),
            $input,
            $message,
        );
    }
}
