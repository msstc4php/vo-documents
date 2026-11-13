<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\OmsPolicyNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "OMS policy". The sources publish no example number; the numbers below are
 * computed with the HL7 Mod10 rule, including the HL7 worked example (12345 → 5).
 */
#[CoversClass(OmsPolicyNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class OmsPolicyNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'digits only' => ['7700000000123453', '7700000000123453'];
        yield 'spaces' => ['7700 0000 0012 3453', '7700000000123453'];
        yield 'hyphens, tab and NBSP' => ["7700-0000\t0012\u{00A0}3453", '7700000000123453'];
        yield 'HL7 worked example padded' => ['0000000000123455', '0000000000123455'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $policy = OmsPolicyNumber::from($input);

        self::assertSame($expected, $policy->value);
        self::assertSame($expected, (string) $policy);
        self::assertSame($expected, $policy->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $policy = OmsPolicyNumber::from('7700000000123453');

        self::assertSame('7700000000123453', $policy->formatted());
        self::assertSame('************3453', $policy->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'control digit changed' => ['7700000000123454', 'Invalid OMS policy number checksum.'];
        yield 'number digit changed' => ['7700000000133453', 'Invalid OMS policy number checksum.'];
        yield 'too short' => ['770000000012345', 'Invalid OMS policy number (15 characters).'];
        yield 'too long' => ['77000000001234530', 'Invalid OMS policy number (17 characters).'];
        yield 'letters' => ['770000000012345O', 'Invalid OMS policy number (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): OmsPolicyNumber => OmsPolicyNumber::from($value),
            $input,
            $message,
        );
    }
}
