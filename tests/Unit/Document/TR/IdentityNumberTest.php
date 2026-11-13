<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\TR;

use MSSTC4PHP\ValueObject\Documents\Document\TR\IdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/TR.md "T.C. Kimlik No"; the examples are the PRADO passport specimens.
 */
#[CoversClass(IdentityNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class IdentityNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield '2010 passport specimen' => ['12345678901', '12345678901'];
        yield '2018 passport specimen' => ['12345678902', '12345678902'];
        yield 'check digits of the unofficial formula do not matter' => ['10000000000', '10000000000'];
        yield 'leading zero is not refused (no official rule)' => ['01234567890', '01234567890'];
        yield 'spaces, NBSP and hyphen' => ["123 4567\u{00A0}89-01", '12345678901'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsWithoutVerifyingTheCheckDigits(string $input, string $expected): void
    {
        $number = IdentityNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFourDigits(): void
    {
        self::assertSame('*******8901', IdentityNumber::from('12345678901')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'ten digits' => ['1234567890', 'Invalid Turkish identity number (10 characters).'];
        yield 'twelve digits' => ['123456789012', 'Invalid Turkish identity number (12 characters).'];
        yield 'letter' => ['1234567890A', 'Invalid Turkish identity number (invalid character).'];
        yield 'empty' => ['', 'Invalid Turkish identity number (0 characters).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid Turkish identity number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): IdentityNumber => IdentityNumber::from($value),
            $input,
            $message,
        );
    }
}
