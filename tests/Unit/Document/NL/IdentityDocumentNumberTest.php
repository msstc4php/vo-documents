<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\NL;

use MSSTC4PHP\ValueObject\Documents\Document\NL\IdentityDocumentNumber;
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
 * Expectations follow docs/sources/NL.md "Passport and identity card number". `SPECI2024` is the number printed on the
 * RvIG model 2024 passport specimen; the others are built from the published layout.
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
        yield 'RvIG specimen' => ['SPECI2024', 'SPECI2024'];
        yield 'letters and digits' => ['NW1K2PT34', 'NW1K2PT34'];
        yield 'only letters before the last digit' => ['ABCDEFGH1', 'ABCDEFGH1'];
        yield 'digits in 3-8' => ['XR1234565', 'XR1234565'];
        yield 'digit zero (model 2014)' => ['XR1204560', 'XR1204560'];
        yield 'lower case' => ['speci2024', 'SPECI2024'];
        yield 'spaces, hyphen, NBSP' => ["SP ECI-20\u{00A0}24", 'SPECI2024'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $number = IdentityDocumentNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasks(): void
    {
        self::assertSame('*****2024', IdentityDocumentNumber::from('SPECI2024')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid Dutch passport or identity card number (invalid character).';

        yield 'eight characters' => ['SPECI202', 'Invalid Dutch passport or identity card number (8 characters).'];
        yield 'ten characters' => ['SPECI20245', 'Invalid Dutch passport or identity card number (10 characters).'];
        yield 'letter O' => ['SPOCI2024', $invalid];
        yield 'letter O first' => ['OPECI2024', $invalid];
        yield 'digit first' => ['1PECI2024', $invalid];
        yield 'digit second' => ['S1ECI2024', $invalid];
        yield 'letter last' => ['SPECI202A', $invalid];
        yield 'umlaut' => ['SPÄCI2024', $invalid];
        yield 'empty' => ['', 'Invalid Dutch passport or identity card number (0 characters).'];
        yield 'far too long' => [\str_repeat('A', 50), 'Invalid Dutch passport or identity card number (too long).'];
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
