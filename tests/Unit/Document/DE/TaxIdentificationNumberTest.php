<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Document\DE\TaxIdentificationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/DE.md "Steuerliche Identifikationsnummer" (format only).
 */
#[CoversClass(TaxIdentificationNumber::class)]
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
        // Format only: no published algorithm, so any last digit is accepted.
        yield 'last digit changed, accepted' => ['12345678902', '12345678902'];
        // The "no leading zero" rule of the unpublished BZSt paper is not applied.
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

    public function testMasks(): void
    {
        self::assertSame('*******8901', TaxIdentificationNumber::from('12345678901')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'ten digits' => ['1234567890', 'Invalid German tax identification number (10 characters).'];
        yield 'twelve digits' => ['123456789012', 'Invalid German tax identification number (12 characters).'];
        yield 'letter' => ['1234567890X', 'Invalid German tax identification number (invalid character).'];
        yield 'empty' => ['', 'Invalid German tax identification number (0 characters).'];
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
