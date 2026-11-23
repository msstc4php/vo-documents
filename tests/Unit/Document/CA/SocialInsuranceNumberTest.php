<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Document\CA\SocialInsuranceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CA.md "Social Insurance Number": format only (no check digit published by ESDC,
 * no first digit refused); a SIN beginning with 9 is a temporary resident's, as ESDC states.
 */
#[CoversClass(SocialInsuranceNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class SocialInsuranceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'CRA sample, display form' => ['123 456 789', '123456789'];
        yield 'CRA sample for Nova Scotia (fails the Luhn sum, accepted)' => ['111 111 111', '111111111'];
        yield 'synthetic, hyphens' => ['024-681-357', '024681357'];
        yield 'synthetic, compact' => ['024681357', '024681357'];
        yield 'synthetic, tabs and NBSP' => ["024\t681\u{00A0}357", '024681357'];
        yield 'leading 8 (no ESDC rule)' => ['800 000 002', '800000002'];
        yield 'temporary resident' => ['912 345 678', '912345678'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = SocialInsuranceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $number = SocialInsuranceNumber::from('123456789');

        self::assertSame('123 456 789', $number->formatted());
        self::assertSame('*** **6 789', $number->masked());
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function temporaryResidentNumbers(): iterable
    {
        yield 'begins with 9' => ['912 345 678', true];
        yield 'nine only at the end' => ['123 456 789', false];
        yield 'begins with 8' => ['812 345 678', false];
        yield 'begins with 0' => ['024 681 357', false];
    }

    #[DataProvider('temporaryResidentNumbers')]
    public function testTellsATemporaryResidentsSinByItsFirstDigit(string $input, bool $expected): void
    {
        self::assertSame($expected, SocialInsuranceNumber::from($input)->isTemporaryResident());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'eight digits' => ['123 456 78', 'Invalid Canadian Social Insurance Number (wrong length).'];
        yield 'ten digits' => ['123 456 7890', 'Invalid Canadian Social Insurance Number (wrong length).'];
        yield 'letter' => ['123 456 78O', 'Invalid Canadian Social Insurance Number (invalid character).'];
        yield 'non-ASCII digit' => [
            "12345678\u{0969}",
            'Invalid Canadian Social Insurance Number (invalid character).',
        ];
        yield 'empty' => ['', 'Invalid Canadian Social Insurance Number (wrong length).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid Canadian Social Insurance Number (wrong length).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): SocialInsuranceNumber => SocialInsuranceNumber::from($value),
            $input,
            $message,
        );
    }
}
