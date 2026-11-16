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
        yield 'one digit' => ['1-2', '12'];
        yield 'seven digits' => ['1.234.567-2', '12345672'];
        yield 'leading zeros kept' => ['00000001-2', '000000012'];
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

    public function testMasksAllButTheLastFourCharactersOfANineCharacterNumber(): void
    {
        self::assertSame('*****6782', SpIdentityCardNumber::from('123456782')->masked());
    }

    public function testMasksAllButTheLastThreeCharactersOfAnEightCharacterNumber(): void
    {
        self::assertSame('*****672', SpIdentityCardNumber::from('12345672')->masked());
    }

    public function testEqualsTheSameNumberWithSeparators(): void
    {
        self::assertTrue(SpIdentityCardNumber::from('12.345.678-2')->equals(SpIdentityCardNumber::from('123456782')));
    }
}
