<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CH;

use MSSTC4PHP\ValueObject\Documents\Document\CH\IdentityDocumentNumber;
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
 * Expectations follow docs/sources/CH.md "Passport and identity card number". `S1A00A00` is the number of the fedpol
 * identity card specimen; the others are built from the published rule.
 */
#[CoversClass(IdentityDocumentNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class IdentityDocumentNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'fedpol identity card specimen' => ['S1A00A00', 'S1A00A00'];
        yield 'one letter and seven digits' => ['X0123456', 'X0123456'];
        yield 'digits only' => ['12345678', '12345678'];
        yield 'letters only' => ['ABCDEFGH', 'ABCDEFGH'];
        yield 'last letters of the alphabet' => ['JKLMNPQZ', 'JKLMNPQZ'];
        yield 'lower case upper-cased' => ['s1a00a00', 'S1A00A00'];
        yield 'spaces and NBSP' => ["S1A0\u{00A0}0A 00", 'S1A00A00'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = IdentityDocumentNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
    }

    public function testMasksAllButTheLastThreeCharacters(): void
    {
        self::assertSame('*****A00', IdentityDocumentNumber::from('S1A00A00')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid Swiss passport or identity card number (invalid character).';

        yield 'letter O' => ['S1A0OA00', $invalid];
        yield 'letter I' => ['S1AI0A00', $invalid];
        yield 'umlaut' => ["S1\u{00C4}00A00", $invalid];
        yield 'slash' => ['S1A00/00', $invalid];
        yield 'seven characters' => ['S1A00A0', 'Invalid Swiss passport or identity card number (7 characters).'];
        yield 'nine characters' => ['S1A00A001', 'Invalid Swiss passport or identity card number (9 characters).'];
        yield 'empty' => ['', 'Invalid Swiss passport or identity card number (0 characters).'];
        yield 'far too long' => [\str_repeat('A1', 30), 'Invalid Swiss passport or identity card number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): IdentityDocumentNumber => IdentityDocumentNumber::from($value),
            $input,
            $message,
        );
    }
}
