<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/BR.md "CNH": nine characters and two check digits (CONTRAN Resolução nº
 * 1.020/2025, art. 10, I), format only. The numbers are synthetic.
 */
#[CoversClass(DriverLicenceNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class DriverLicenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'eleven digits' => ['12345678901', '12345678901'];
        yield 'leading zeros kept' => ['00123456789', '00123456789'];
        yield 'separators' => ['123 456 789-01', '12345678901'];
        yield 'letters among the nine characters' => ['ab345678901', 'AB345678901'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = DriverLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksKeepingFourCharacters(): void
    {
        self::assertSame('*******8901', DriverLicenceNumber::from('12345678901')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Brazilian driving licence number (invalid character).';

        yield 'letter in a check digit' => ['1234567890A', $character];
        yield 'letter in the first check digit' => ['123456789A1', $character];
        yield 'symbol' => ['12345678#01', $character];
        yield 'ten characters' => ['1234567890', 'Invalid Brazilian driving licence number (10 characters).'];
        yield 'twelve characters' => ['123456789012', 'Invalid Brazilian driving licence number (12 characters).'];
        yield 'empty' => ['', 'Invalid Brazilian driving licence number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): DriverLicenceNumber => DriverLicenceNumber::from($value),
            $input,
            $message,
        );
    }
}
