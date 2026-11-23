<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\MX\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\MexDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\MX\Jurisdiction;
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
 * Expectations follow docs/sources/MX.md "State driving licences — per entity": the Edomex validator's rule
 * "El Número de Licencia es de 12 dígitos" / "debe contener solo números"; the numbers are made up (no official
 * sample is published).
 */
#[CoversClass(AbstractDriverLicenceNumber::class)]
#[CoversClass(MexDriverLicenceNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Text::class)]
final class DriverLicenceNumbersTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    private const int LONGEST_TRIED = 16;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'twelve digits' => ['123456789012', '123456789012'];
        yield 'leading zeros kept' => ['000000000001', '000000000001'];
        yield 'separators, tabs and NBSP' => [" 1234-5678\t90\u{00A0}12 ", '123456789012'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheNormalisedNumber(string $input, string $expected): void
    {
        $number = MexDriverLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testTellsItsJurisdiction(): void
    {
        self::assertSame(Jurisdiction::Mex, MexDriverLicenceNumber::from('123456789012')->jurisdiction());
    }

    public function testRefusesEveryOtherLength(): void
    {
        for ($length = 0; $length <= self::LONGEST_TRIED; $length++) {
            $input = \str_repeat('1', $length);
            self::assertSame($length === 12, MexDriverLicenceNumber::fitsLength($input));
            if ($length === 12) {
                continue;
            }

            self::assertRefusedWithoutEcho(
                static fn (string $value): AbstractDriverLicenceNumber => MexDriverLicenceNumber::from($value),
                $input,
                "Invalid MX-MEX driver's licence number (wrong length).",
            );
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function refusedNumbers(): iterable
    {
        $character = "Invalid MX-MEX driver's licence number (invalid character).";

        yield 'letter' => ['12345678901A', $character];
        yield 'letter first' => ['A23456789012', $character];
        yield 'other separator' => ['123456.78901', $character];
        yield 'pasted several times' => [
            \str_repeat('123456789012 ', 5),
            "Invalid MX-MEX driver's licence number (wrong length).",
        ];
    }

    #[DataProvider('refusedNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractDriverLicenceNumber => MexDriverLicenceNumber::from($value),
            $input,
            $message,
        );
    }

    public function testMasksAllButTheLastFourDigits(): void
    {
        self::assertSame('********9012', MexDriverLicenceNumber::from('123456789012')->masked());
    }

    public function testEqualsTheSameNumberWithSeparators(): void
    {
        self::assertTrue(
            MexDriverLicenceNumber::from('1234 5678 9012')->equals(MexDriverLicenceNumber::from('123456789012')),
        );
    }
}
