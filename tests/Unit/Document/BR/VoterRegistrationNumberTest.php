<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\VoterRegistrationNumber;
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
 * Expectations follow docs/sources/BR.md "Título de eleitor" (TSE Resolução nº 23.659/2021, art. 36). The TSE gives
 * no example number, so the numbers are synthetic; their check digits are not verified (format only).
 */
#[CoversClass(VoterRegistrationNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class VoterRegistrationNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'São Paulo, twelve digits' => ['1234 5678 0191', '123456780191', '01'];
        yield 'leading zeros dropped on issue' => ['4567 0191', '000045670191', '01'];
        yield 'one running digit' => ['70191', '000000070191', '01'];
        yield 'Tocantins' => ['123456782755', '123456782755', '27'];
        yield 'abroad' => ['000012342812', '000012342812', '28'];
        yield 'any check digits' => ['123456780100', '123456780100', '01'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndReadsTheStateCode(string $input, string $expected, string $state): void
    {
        $number = VoterRegistrationNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
        self::assertSame($state, $number->stateCode());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function tableCodes(): iterable
    {
        foreach (\range(1, 28) as $code) {
            $state = \sprintf('%02d', $code);

            yield 'code ' . $state => [$state];
        }
    }

    #[DataProvider('tableCodes')]
    public function testAcceptsEveryCodeOfTheTseTable(string $state): void
    {
        self::assertSame($state, VoterRegistrationNumber::from('12345678' . $state . '00')->stateCode());
    }

    public function testAShortNumberEqualsItsZeroPaddedForm(): void
    {
        self::assertTrue(VoterRegistrationNumber::from('45670191')->equals(VoterRegistrationNumber::from('000045670191')));
    }

    public function testTellsARegistrationAbroad(): void
    {
        self::assertTrue(VoterRegistrationNumber::from('000012342812')->isAbroad());
        self::assertFalse(VoterRegistrationNumber::from('000012342712')->isAbroad());
        self::assertFalse(VoterRegistrationNumber::from('000012340112')->isAbroad());
    }

    public function testMasksKeepingFourDigits(): void
    {
        self::assertSame('********0191', VoterRegistrationNumber::from('123456780191')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $state = 'Invalid Brazilian voter registration number (unknown state code).';

        yield 'state code 00' => ['123456780012', $state];
        yield 'state code 29' => ['123456782912', $state];
        yield 'state code 30' => ['123456783012', $state];
        yield 'state code 99' => ['123456789912', $state];
        yield 'four digits, no running number' => ['0191', 'Invalid Brazilian voter registration number (4 characters).'];
        yield 'thirteen digits' => ['1234567801912', 'Invalid Brazilian voter registration number (13 characters).'];
        yield 'letter' => ['12345678019A', 'Invalid Brazilian voter registration number (invalid character).'];
        yield 'empty' => ['', 'Invalid Brazilian voter registration number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): VoterRegistrationNumber => VoterRegistrationNumber::from($value),
            $input,
            $message,
        );
    }
}
