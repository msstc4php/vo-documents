<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\ID;

use MSSTC4PHP\ValueObject\Documents\Document\ID\TaxpayerNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/ID.md "NPWP": the 15-digit form 99.999.999.9-999.999 and, since PMK 112/2022, the
 * 16-digit NIK of an individual resident or a 0 in front of the 15 digits.
 */
#[CoversClass(TaxpayerNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class TaxpayerNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function validNumbers(): iterable
    {
        yield 'DJP form of 15 digits' => ['01.234.567.8-901.000', '012345678901000', true];
        yield '15 digits without separators' => ['012345678901000', '012345678901000', true];
        yield '15 digits with spaces' => ['01 234 567 8 901 000', '012345678901000', true];
        yield '15 digits, NBSP' => ["01.234.567.8-901.000\u{00A0}", '012345678901000', true];
        yield '15 digits, fullwidth digits' => [
            "\u{FF10}\u{FF11}.\u{FF12}\u{FF13}\u{FF14}.\u{FF15}\u{FF16}\u{FF17}.\u{FF18}-\u{FF19}\u{FF10}\u{FF11}" .
                ".\u{FF10}\u{FF10}\u{FF10}",
            '012345678901000',
            true,
        ];
        yield 'hyphen-less mask of the same groups' => ['01.234.567.8901.000', '012345678901000', true];
        yield 'NIK of 16 digits' => ['3171011505980001', '3171011505980001', false];
        yield '0 in front of 15 digits' => ['0012345678901000', '0012345678901000', false];
        yield '16 digits with spaces' => ['3171 0115 0598 0001', '3171011505980001', false];
        yield '16 digits, fullwidth' => [
            "\u{FF13}\u{FF11}\u{FF17}\u{FF11}\u{FF10}\u{FF11}\u{FF11}\u{FF15}\u{FF10}\u{FF15}\u{FF19}\u{FF18}" .
                "\u{FF10}\u{FF10}\u{FF10}\u{FF11}",
            '3171011505980001',
            false,
        ];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsBothLengths(string $input, string $expected, bool $fifteen): void
    {
        $number = TaxpayerNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
        self::assertSame($fifteen, $number->isFifteenDigits());
    }

    public function testFormatsFifteenDigitsAsDjpPrintsThem(): void
    {
        self::assertSame('01.234.567.8-901.000', TaxpayerNumber::from('012345678901000')->formatted());
    }

    public function testSixteenDigitsHaveNoQuotedDisplayForm(): void
    {
        self::assertSame('3171011505980001', TaxpayerNumber::from('3171 0115 0598 0001')->formatted());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('**.***.***.*-**1.000', TaxpayerNumber::from('012345678901000')->masked());
        self::assertSame('************0001', TaxpayerNumber::from('3171011505980001')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Indonesian taxpayer number (invalid character).';
        $placeholder = 'Invalid Indonesian taxpayer number (placeholder, not a number).';

        yield 'the 15-digit "no number" DJP prints' => ['00.000.000.0-000.000', $placeholder];
        yield 'the same without separators' => ['000000000000000', $placeholder];
        yield 'the same split over two lines' => ["00.000.000.0-\n000.000", $placeholder];
        yield 'the 16-digit "no number" DJP prints' => ['0000000000000000', $placeholder];
        yield 'the 16 zeros with spaces' => ['0000 0000 0000 0000', $placeholder];
        yield 'empty' => ['', 'Invalid Indonesian taxpayer number (0 characters).'];
        yield '14 digits' => ['01234567890100', 'Invalid Indonesian taxpayer number (14 characters).'];
        yield '17 digits' => ['31710115059800011', 'Invalid Indonesian taxpayer number (17 characters).'];
        yield '9 digits, the old form of a 9-digit NPWP' => [
            '012345678',
            'Invalid Indonesian taxpayer number (9 characters).',
        ];
        yield 'far too long' => [\str_repeat('1', 41), 'Invalid Indonesian taxpayer number (too long).'];
        yield 'letter in 15' => ['01234567890100X', $character];
        yield 'letter in 16' => ['317101150598000X', $character];
        yield 'comma' => ['01,234,567,8,901,000', 'Invalid Indonesian taxpayer number (20 characters).'];
        yield 'comma in a 15-digit body' => ['01234567,8901000', $character];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): TaxpayerNumber => TaxpayerNumber::from($value),
            $input,
            $message,
        );
    }
}
