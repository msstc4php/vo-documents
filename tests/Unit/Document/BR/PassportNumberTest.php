<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\PassportNumber;
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
 * Expectations follow docs/sources/BR.md "Passport number": two letters (the "série") and six digits; `CS265436` is
 * the Polícia Federal's example.
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(NumberShapes::class)]
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
        yield 'Polícia Federal example' => ['CS265436', 'CS265436'];
        yield 'lower case' => ['cs265436', 'CS265436'];
        yield 'separators' => ['CS 265 436', 'CS265436'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testReadsTheSeriesAndMasksKeepingThreeCharacters(): void
    {
        $number = PassportNumber::from('CS265436');

        self::assertSame('CS', $number->series());
        self::assertSame('*****436', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Brazilian passport number (invalid character).';

        yield 'one letter' => ['C2654361', $character];
        yield 'three letters' => ['CSA65436', $character];
        yield 'eight digits' => ['12265436', $character];
        yield 'letter at the end' => ['CS26543A', $character];
        yield 'seven characters' => ['CS26543', 'Invalid Brazilian passport number (7 characters).'];
        yield 'nine characters' => ['CS2654361', 'Invalid Brazilian passport number (9 characters).'];
        yield 'empty' => ['', 'Invalid Brazilian passport number (0 characters).'];
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
