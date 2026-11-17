<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CN;

use MSSTC4PHP\ValueObject\Documents\Document\CN\LegacyForeignerPermanentResidenceNumber;
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
 * Expectations follow docs/sources/CN.md "Foreigner's permanent residence ID card".
 */
#[CoversClass(LegacyForeignerPermanentResidenceNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class LegacyForeignerPermanentResidenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'NIA example' => ['KAZ110090123105', 'KAZ110090123105'];
        yield 'lower case and separators' => ['kaz 1100-9012 3105', 'KAZ110090123105'];
        yield 'fullwidth' => ["\u{FF2B}\u{FF21}\u{FF3A}110090123105", 'KAZ110090123105'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = LegacyForeignerPermanentResidenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testReadsTheNationalityLetters(): void
    {
        self::assertSame('KAZ', LegacyForeignerPermanentResidenceNumber::from('KAZ110090123105')->nationalityCode());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('***********3105', LegacyForeignerPermanentResidenceNumber::from('KAZ110090123105')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Chinese legacy permanent residence card number (invalid character).';

        yield 'two letters and 13 digits' => ['KA1100901231054', $character];
        yield 'four letters and 11 digits' => ['KAZA10090123105', $character];
        yield 'digits only' => ['123110090123105', $character];
        yield 'letter among the digits' => ['KAZ11009012310A', $character];
        yield 'fourteen characters' => ['KAZ11009012310', 'Invalid Chinese legacy permanent residence card number (14 characters).'];
        yield 'sixteen characters' => ['KAZ1100901231051', 'Invalid Chinese legacy permanent residence card number (16 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): LegacyForeignerPermanentResidenceNumber => LegacyForeignerPermanentResidenceNumber::from($value), $input, $message);
    }
}
