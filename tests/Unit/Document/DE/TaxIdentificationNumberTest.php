<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Document\DE\TaxIdentificationNumber;
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
 * Expectations follow docs/sources/DE.md "Steuerliche Identifikationsnummer": format only for `from()`, the ELSTER
 * check digit reported by `hasValidCheckDigit()`.
 */
#[CoversClass(TaxIdentificationNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class TaxIdentificationNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'eleven digits' => ['12345678901', '12345678901'];
        yield 'spaces and NBSP' => ["12 345 678\u{00A0}901", '12345678901'];
        // from() checks the format only (1.x), so a wrong check digit is accepted.
        yield 'last digit changed, accepted' => ['12345678902', '12345678902'];
        // The ELSTER structure rules are not applied.
        yield 'leading zero, accepted' => ['02345678901', '02345678901'];
        yield 'no repeated digit, accepted' => ['10234567895', '10234567895'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = TaxIdentificationNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function elsterExamples(): iterable
    {
        yield 'TestID, leading zero' => ['02476291358'];
        yield 'ITZ Bund 1' => ['86095742719'];
        yield 'ITZ Bund 2' => ['47036892816'];
        yield 'ITZ Bund 3, two adjacent 9s' => ['65929970489'];
        yield 'ITZ Bund 4' => ['57549285017'];
        yield 'ITZ Bund 5' => ['25768131411'];
    }

    #[DataProvider('elsterExamples')]
    public function testReportsTheElsterExamplesAsValid(string $number): void
    {
        self::assertTrue(TaxIdentificationNumber::from($number)->hasValidCheckDigit());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function wrongCheckDigits(): iterable
    {
        yield 'last digit off by one' => ['86095742718'];
        yield 'last digit zero' => ['86095742710'];
        yield 'two leading digits swapped' => ['68095742719'];
    }

    #[DataProvider('wrongCheckDigits')]
    public function testReportsAWrongCheckDigitAndStillAcceptsIt(string $number): void
    {
        $id = TaxIdentificationNumber::from($number);

        self::assertFalse($id->hasValidCheckDigit());
        self::assertSame($number, $id->value);
    }

    public function testReadsAComputedTenAsZero(): void
    {
        self::assertTrue(TaxIdentificationNumber::from('10000000000')->hasValidCheckDigit());
    }

    public function testMasks(): void
    {
        self::assertSame('*******8901', TaxIdentificationNumber::from('12345678901')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'ten digits' => ['1234567890', 'Invalid German tax identification number (wrong length).'];
        yield 'twelve digits' => ['123456789012', 'Invalid German tax identification number (wrong length).'];
        yield 'letter' => ['1234567890X', 'Invalid German tax identification number (invalid character).'];
        yield 'empty' => ['', 'Invalid German tax identification number (wrong length).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): TaxIdentificationNumber => TaxIdentificationNumber::from($value),
            $input,
            $message,
        );
    }
}
