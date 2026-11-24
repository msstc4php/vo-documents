<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Document\AU\TaxFileNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/AU.md "Tax file number".
 */
#[CoversClass(TaxFileNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class TaxFileNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'worked example' => ['123 456 782', '123456782'];
        yield 'another nine digits' => ['330 600 376', '330600376'];
        yield 'digits only' => ['123456782', '123456782'];
        yield 'hyphens, tabs and NBSP' => ["123-456\t78\u{00A0}2", '123456782'];
        // Format only: no official algorithm is published, so a "wrong" check digit is accepted.
        yield 'last digit changed, accepted' => ['123 456 783', '123456783'];
        yield 'first digit changed, accepted' => ['223 456 782', '223456782'];
        // ANAO § 1.5: "a eight or nine digit number"; kept as written, not padded.
        yield 'eight digits' => ['12 345 678', '12345678'];
        yield 'eight digits, leading zero' => ['01234567', '01234567'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $tfn = TaxFileNumber::from($input);

        self::assertSame($expected, $tfn->value);
        self::assertSame($expected, (string) $tfn);
        self::assertSame($expected, $tfn->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function displayForms(): iterable
    {
        yield 'nine digits' => ['123456782', '123 456 782', '*** *** 782'];
        yield 'eight digits, last two shown' => ['12345678', '12 345 678', '** *** *78'];
    }

    #[DataProvider('displayForms')]
    public function testFormatsAndMasks(string $input, string $formatted, string $masked): void
    {
        $tfn = TaxFileNumber::from($input);

        self::assertSame($formatted, $tfn->formatted());
        self::assertSame($masked, $tfn->masked());
    }

    public function testEightDigitsAreNotPaddedSoTheyDifferFromTheZeroPaddedForm(): void
    {
        self::assertFalse(TaxFileNumber::from('12345678')->equals(TaxFileNumber::from('012345678')));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'seven digits' => ['1 234 567', 'Invalid Australian tax file number (wrong length).'];
        yield 'eight characters, letter' => ['1234567O', 'Invalid Australian tax file number (invalid character).'];
        yield 'eight characters, multibyte letter' => ['1234567Ö', 'Invalid Australian tax file number (invalid character).'];
        yield 'ten digits' => ['123 456 7820', 'Invalid Australian tax file number (wrong length).'];
        yield 'letter' => ['123 456 78O', 'Invalid Australian tax file number (invalid character).'];
        yield 'empty' => ['', 'Invalid Australian tax file number (wrong length).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): TaxFileNumber => TaxFileNumber::from($value),
            $input,
            $message,
        );
    }
}
