<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Document\DE\PassportNumber;
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
 * Expectations follow docs/sources/DE.md "Reisepass number" (the Muster number is an image only, so the numbers
 * are built from the PassV alphabet).
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(NumberFormat::class)]
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
        yield 'letters and digits' => ['LZ6311T47', 'LZ6311T47'];
        yield 'digits only' => ['123456789', '123456789'];
        yield 'every allowed letter' => ['CFGHJKLMN', 'CFGHJKLMN'];
        yield 'the other allowed letters' => ['PRTVWXYZ9', 'PRTVWXYZ9'];
        yield 'lower case' => ['lz6311t47', 'LZ6311T47'];
        yield 'spaces, hyphen, NBSP' => ["LZ63 11-T\u{00A0}47", 'LZ6311T47'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasks(): void
    {
        self::assertSame('*****1T47', PassportNumber::from('LZ6311T47')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid German passport number (invalid character).';

        yield 'eight characters' => ['LZ6311T4', 'Invalid German passport number (8 characters).'];
        yield 'ten characters (with the MRZ check digit)' => [
            'LZ6311T475',
            'Invalid German passport number (10 characters).',
        ];
        yield 'provisional passport, letter and seven digits' => [
            'A1234567',
            'Invalid German passport number (8 characters).',
        ];
        yield 'digit zero' => ['LZ6301T47', $invalid];
        yield 'letter O' => ['LZ63O1T47', $invalid];
        yield 'vowel A' => ['LA6311T47', $invalid];
        yield 'letter B' => ['LB6311T47', $invalid];
        yield 'letter D' => ['LD6311T47', $invalid];
        yield 'letter Q' => ['LQ6311T47', $invalid];
        yield 'letter S' => ['LS6311T47', $invalid];
        yield 'umlaut' => ['LÜ6311T47', $invalid];
        yield 'empty' => ['', 'Invalid German passport number (0 characters).'];
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
