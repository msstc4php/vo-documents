<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR\IdentityCard;

use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\AbstractIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\SpIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\BR\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
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
 * Expectations follow docs/sources/BR.md "RG per state — per unit": the SSP-SP form's number range
 * (`verificaRange(…, 1, 99999999)`), check-character set (`"0123456789xX"`) and `checaRG`. No official sample is
 * published; the check characters below are computed with `checaRG` by the Task 7b script.
 */
#[CoversClass(AbstractIdentityCardNumber::class)]
#[CoversClass(SpIdentityCardNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Text::class)]
final class IdentityCardNumbersTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    private const int LONGEST_TRIED = 16;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'printed form' => ['12.345.678-2', '123456782'];
        yield 'bare' => ['123456782', '123456782'];
        yield 'check character X' => ['24.681.357-X', '24681357X'];
        yield 'check character x upper-cased' => ['24681357x', '24681357X'];
        yield 'check character 0' => ['99.999.999-0', '999999990'];
        yield 'check character X after a zero' => ['10000006-X', '10000006X'];
        yield 'one digit, padded to eight' => ['1-2', '000000012'];
        yield 'seven digits, padded to eight' => ['1.234.567-2', '012345672'];
        yield 'leading zeros kept' => ['00000001-2', '000000012'];
        yield 'leading zero typed' => ['01.234.567-2', '012345672'];
        yield 'separators, tabs and NBSP' => [" 12 345\t678\u{00A0}2 ", '123456782'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheNormalisedNumber(string $input, string $expected): void
    {
        $number = SpIdentityCardNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testTellsItsJurisdiction(): void
    {
        self::assertSame(Jurisdiction::Sp, SpIdentityCardNumber::from('123456782')->jurisdiction());
    }

    public function testRefusesEveryOtherLength(): void
    {
        for ($length = 0; $length <= self::LONGEST_TRIED; $length++) {
            $input = \str_repeat('1', $length);
            $fits = $length >= 2 && $length <= 9;
            self::assertSame($fits, SpIdentityCardNumber::fitsLength($input));
            self::assertSame($fits, SpIdentityCardNumber::fitsLength(\implode('.', \str_split($input))));
            if ($fits) {
                continue;
            }

            self::assertRefusedWithoutEcho(
                static fn (string $value): AbstractIdentityCardNumber => SpIdentityCardNumber::from($value),
                $input,
                \sprintf('Invalid BR-SP identity card number (%d characters).', $length),
            );
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function refusedNumbers(): iterable
    {
        $character = 'Invalid BR-SP identity card number (invalid character).';
        $checksum = 'Invalid BR-SP identity card number checksum.';
        $zeros = 'Invalid BR-SP identity card number (zeros only).';

        yield 'wrong check digit' => ['12.345.678-3', $checksum];
        yield 'X where a digit is due' => ['12.345.678-X', $checksum];
        yield 'digit where X is due' => ['24.681.357-0', $checksum];
        yield 'one digit, wrong check' => ['1-3', $checksum];
        yield 'letter other than X as check' => ['12345678A', $character];
        yield 'X inside the number' => ['1234X6782', $character];
        yield 'X first' => ['X2', $character];
        yield 'other separator' => ['123/45678', $character];
        yield 'zeros only' => ['0-0', $zeros];
        yield 'eight zeros' => ['00.000.000-0', $zeros];
        yield 'zeros only, any check' => ['0000-5', $zeros];
        yield 'pasted several times' => [
            \str_repeat('12.345.678-2 ', 5),
            'Invalid BR-SP identity card number (too long).',
        ];
    }

    #[DataProvider('refusedNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractIdentityCardNumber => SpIdentityCardNumber::from($value),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function maskedValues(): iterable
    {
        yield 'eight digits' => ['123456782', '*****6782'];
        yield 'seven digits' => ['1.234.567-2', '*****5672'];
        yield 'four digits' => ['1234-8', '*****2348'];
        yield 'three digits: never every real digit' => ['123-5', '******235'];
        yield 'two digits' => ['10-3', '*******03'];
        yield 'one digit' => ['1-2', '********2'];
        yield 'two digits, check 0' => ['14-0', '*******40'];
    }

    #[DataProvider('maskedValues')]
    public function testMasksAllButTheLastFourAndNeverTheWholeSignificantValue(string $input, string $masked): void
    {
        self::assertSame($masked, SpIdentityCardNumber::from($input)->masked());
    }

    /**
     * Decreto nº 10.977/2022 art. 25 keeps old-model cards valid, and the SSP page admits cards without the check
     * character ("Se na sua Carteira de Identidade não constar o dígito de controle…").
     *
     * @return iterable<string, array{string, ?string, string}>
     */
    public static function formFields(): iterable
    {
        yield 'no check character printed (null)' => ['1234567', null, '012345672'];
        yield 'no check character printed (empty)' => ['1.234.567', '', '012345672'];
        yield 'blank check field' => ['24681357', ' ', '24681357X'];
        yield 'computed X' => ['24.681.357', null, '24681357X'];
        yield 'supplied check character' => ['12.345.678', '2', '123456782'];
        yield 'supplied x upper-cased' => ['24681357', 'x', '24681357X'];
        yield 'leading zeros kept' => ['00000001', null, '000000012'];
    }

    #[DataProvider('formFields')]
    public function testBuildsFromTheFormsTwoFields(string $number, ?string $checkCharacter, string $expected): void
    {
        $card = SpIdentityCardNumber::fromNumberAndCheckCharacter($number, $checkCharacter);

        self::assertSame($expected, $card->value);
        self::assertTrue(SpIdentityCardNumber::from($expected)->equals($card));
    }

    public function testACardWithoutTheCheckCharacterEqualsTheOneWithIt(): void
    {
        self::assertTrue(
            SpIdentityCardNumber::fromNumberAndCheckCharacter('1234567', null)
                ->equals(SpIdentityCardNumber::from('1.234.567-2')),
        );
    }

    /**
     * @return iterable<string, array{SpIdentityCardNumber}>
     */
    public static function spellingsOfOneRg(): iterable
    {
        yield 'form, seven digits' => [SpIdentityCardNumber::fromNumberAndCheckCharacter('1234567', null)];
        yield 'form, leading zero' => [SpIdentityCardNumber::fromNumberAndCheckCharacter('01234567', null)];
        yield 'form, check given' => [SpIdentityCardNumber::fromNumberAndCheckCharacter('1.234.567', '2')];
        yield 'printed, seven digits' => [SpIdentityCardNumber::from('1.234.567-2')];
        yield 'printed, leading zero' => [SpIdentityCardNumber::from('01.234.567-2')];
        yield 'bare, seven digits' => [SpIdentityCardNumber::from('12345672')];
    }

    #[DataProvider('spellingsOfOneRg')]
    public function testStoresEverySpellingOfOneRgTheSameWay(SpIdentityCardNumber $card): void
    {
        self::assertSame('012345672', $card->value);
        self::assertTrue(SpIdentityCardNumber::from('012345672')->equals($card));
        self::assertTrue(SpIdentityCardNumber::from((string) $card)->equals($card));
    }

    public function testAnEightDigitBareNumberRoundTripsThroughFrom(): void
    {
        $card = SpIdentityCardNumber::fromNumberAndCheckCharacter('12345678', null);

        self::assertTrue(SpIdentityCardNumber::from((string) $card)->equals($card));
        self::assertSame('123456782', (string) $card);
    }

    /**
     * @return iterable<string, array{string, ?string, string}>
     */
    public static function refusedFormFields(): iterable
    {
        $document = 'Invalid BR-SP identity card number';

        yield 'wrong supplied check' => ['12.345.678', '3', $document . ' checksum.'];
        yield 'zeros only' => ['00000000', null, $document . ' (zeros only).'];
        yield 'zeros only with a check' => ['0', '0', $document . ' (zeros only).'];
        yield 'nine digits' => ['123456782', null, $document . ' (9 characters).'];
        yield 'empty number' => ['', null, $document . ' (0 characters).'];
        yield 'pasted several times' => [\str_repeat('12.345.678.', 6), null, $document . ' (too long).'];
        yield 'letter in the number' => ['1234567A', null, $document . ' (number field: digits and dots only).'];
        yield 'X in the number' => ['1234567X', '2', $document . ' (number field: digits and dots only).'];
        yield 'hyphen and check digit in the number' => [
            '1234567-2',
            null,
            $document . ' (number field: digits and dots only).',
        ];
        yield 'printed form in the number' => [
            '1.234.567-2',
            null,
            $document . ' (number field: digits and dots only).',
        ];
        yield 'check X in the number' => ['24.681.357-X', null, $document . ' (number field: digits and dots only).'];
        yield 'space in the number' => ['1234 567', null, $document . ' (number field: digits and dots only).'];
        yield 'pasted with spaces' => [
            \str_repeat('12.345.678 ', 6),
            null,
            $document . ' (number field: digits and dots only).',
        ];
        yield 'two check characters' => ['1234567', '22', $document . ' (invalid character).'];
        yield 'letter other than X as check' => ['1234567', 'A', $document . ' (invalid character).'];
        yield 'dot as check' => ['123456', '.', $document . ' (invalid character).'];
    }

    #[DataProvider('refusedFormFields')]
    public function testRefusesBadFormFieldsWithoutEchoingThem(
        string $number,
        ?string $checkCharacter,
        string $message,
    ): void {
        self::assertRefusedWithoutEcho(
            static fn (string $value): SpIdentityCardNumber => SpIdentityCardNumber::fromNumberAndCheckCharacter(
                $value,
                $checkCharacter,
            ),
            $number,
            $message,
        );
    }

    public function testEqualsTheSameNumberWithSeparators(): void
    {
        self::assertTrue(SpIdentityCardNumber::from('12.345.678-2')->equals(SpIdentityCardNumber::from('123456782')));
    }
}
