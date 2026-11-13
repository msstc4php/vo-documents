<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Document\DE\PensionInsuranceNumber;
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
 * Expectations follow docs/sources/DE.md "Rentenversicherungsnummer" (VKVV § 2).
 */
#[CoversClass(PensionInsuranceNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PensionInsuranceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'AOK example' => ['65 230701 J008', '65230701J008'];
        yield 'display form' => ['65 230701 J 00 8', '65230701J008'];
        yield 'lower-case letter, NBSP' => ["65230701\u{00A0}j008", '65230701J008'];
        yield 'letter C (03), check digit 3' => ['15070649C103', '15070649C103'];
        yield 'letter Z (26), serial 99' => ['25300290Z994', '25300290Z994'];
        // VKVV § 2 Abs. 3: unproven birth dates follow unpublished rules, so the field is not validated as a date.
        yield '30 February in the birth date field, accepted' => ['12300230A500', '12300230A500'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheCompactForm(string $input, string $expected): void
    {
        $number = PensionInsuranceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testExposesTheFieldsAsPrinted(): void
    {
        $number = PensionInsuranceNumber::from('65230701J008');

        self::assertSame('65', $number->areaNumber());
        self::assertSame('230701', $number->birthDateField());
        self::assertSame('J', $number->birthNameInitial());
        self::assertSame('00', $number->serialNumber());
    }

    public function testFormatsAndMasks(): void
    {
        $number = PensionInsuranceNumber::from('65230701J008');

        self::assertSame('65 230701 J 00 8', $number->formatted());
        self::assertSame('** ****** J 00 8', $number->masked());
        self::assertSame('** ****** Z 99 4', PensionInsuranceNumber::from('25300290Z994')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid German pension insurance number (invalid character).';
        $checksum = 'Invalid German pension insurance number checksum.';

        // The DRV glossary prints an illustrative number whose check digit is not the VKVV § 2 Abs. 6 result (1).
        yield 'DRV glossary example' => ['65 170839 J 08 8', $checksum];
        yield 'check digit changed' => ['65230701J009', $checksum];
        yield 'serial changed' => ['65230701J018', $checksum];
        yield 'letter changed' => ['65230701K008', $checksum];
        yield 'area number changed' => ['66230701J008', $checksum];
        yield 'eleven characters' => ['65230701J00', 'Invalid German pension insurance number (11 characters).'];
        yield 'thirteen characters' => ['65230701J0088', 'Invalid German pension insurance number (13 characters).'];
        yield 'digit at the letter position' => ['652307011008', $invalid];
        yield 'letter in the birth date' => ['6523070JJ008', $invalid];
        yield 'letter in the area number' => ['J5230701J008', $invalid];
        yield 'letter in the serial' => ['65230701J0J8', $invalid];
        yield 'letter as the check digit' => ['65230701J00J', $invalid];
        yield 'umlaut at the letter position' => ['65230701Ä008', $invalid];
        yield 'empty' => ['', 'Invalid German pension insurance number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): PensionInsuranceNumber => PensionInsuranceNumber::from($value),
            $input,
            $message,
        );
    }
}
