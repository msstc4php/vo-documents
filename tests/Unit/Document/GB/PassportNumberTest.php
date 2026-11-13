<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Document\GB\PassportNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/GB.md "Passport number"; HMPO publishes no example number.
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PassportNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'compact' => ['123456789', '123456789'];
        yield 'leading zero kept' => ['012345678', '012345678'];
        yield 'spaces and NBSP' => ["123 456\u{00A0}789", '123456789'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFourDigits(): void
    {
        self::assertSame('*****6789', PassportNumber::from('123456789')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'eight digits' => ['12345678', 'Invalid British passport number (8 characters).'];
        yield 'ten digits' => ['1234567890', 'Invalid British passport number (10 characters).'];
        yield 'thin-film serial number' => ['ABC1234X', 'Invalid British passport number (8 characters).'];
        yield 'letter' => ['A12345678', 'Invalid British passport number (invalid character).'];
        yield 'empty' => ['', 'Invalid British passport number (0 characters).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid British passport number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): PassportNumber => PassportNumber::from($value),
            $input,
            $message,
        );
    }
}
