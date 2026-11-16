<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\PassportCardNumber;
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
 * Expectations follow docs/sources/US.md "Passport card number" (`C` + eight digits); the numbers are synthetic.
 */
#[CoversClass(PassportCardNumber::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PassportCardNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'compact' => ['C12345678', 'C12345678'];
        yield 'lower case, separators' => ["c 1234\u{00A0}5678", 'C12345678'];
        yield 'zeros' => ['C00000000', 'C00000000'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        self::assertSame($expected, PassportCardNumber::from($input)->value);
    }

    public function testMasksKeepingFourCharacters(): void
    {
        self::assertSame('*****5678', PassportCardNumber::from('C12345678')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid American passport card number (invalid character).';

        yield 'another letter (a book number)' => ['A12345678', $character];
        yield 'nine digits' => ['123456789', $character];
        yield 'letter among the digits' => ['C1234567X', $character];
        yield 'eight characters' => ['C1234567', 'Invalid American passport card number (8 characters).'];
        yield 'ten characters' => ['C123456789', 'Invalid American passport card number (10 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): PassportCardNumber => PassportCardNumber::from($value),
            $input,
            $message,
        );
    }
}
