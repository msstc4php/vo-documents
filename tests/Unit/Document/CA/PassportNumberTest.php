<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Document\CA\PassportNumber;
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
 * Expectations follow docs/sources/CA.md "Passport number". IRCC publishes the layouts but no example number, so the
 * numbers are synthetic.
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(NumberFormat::class)]
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
        yield 'since May 2023, letter, six digits, two letters' => ['A123456BC', 'A123456BC'];
        yield 'before May 2023, two letters and six digits' => ['AB123456', 'AB123456'];
        yield 'lower case' => ['a123456bc', 'A123456BC'];
        yield 'separators' => ["AB 123\u{00A0}456", 'AB123456'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksKeepingFourOrThreeCharacters(): void
    {
        self::assertSame('*****56BC', PassportNumber::from('A123456BC')->masked());
        self::assertSame('*****456', PassportNumber::from('AB123456')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Canadian passport number (invalid character).';

        yield 'old form followed by a letter' => ['AB123456C', $character];
        yield 'old form after a digit' => ['1AB123456', $character];
        yield 'new form missing the first letter' => ['1234567BC', $character];
        yield 'new form with one final letter' => ['A1234567B', $character];
        yield 'old form with one letter' => ['A1234567', $character];
        yield 'eight digits' => ['12345678', $character];
        yield 'new form with three letters' => ['AB12345CD', $character];
        yield 'letter first then digits' => ['A12345678', $character];
        yield 'non-ASCII digit' => ["AB12345\u{0663}", $character];
        yield 'seven characters' => ['AB12345', 'Invalid Canadian passport number (7 characters).'];
        yield 'ten characters' => ['A123456BCD', 'Invalid Canadian passport number (10 characters).'];
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
