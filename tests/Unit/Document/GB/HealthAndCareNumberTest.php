<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Document\GB\HealthAndCareNumber;
use MSSTC4PHP\ValueObject\Documents\Document\GB\NhsCheckDigit;
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
 * Expectations follow docs/sources/GB.md "Health and Care number, Northern Ireland"; the range edges carry their
 * Modulus 11 check digits.
 */
#[CoversClass(HealthAndCareNumber::class)]
#[UsesClass(NhsCheckDigit::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class HealthAndCareNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'first of the range' => ['320 000 0015', '3200000015'];
        yield 'last of the range' => ['399 999 9993', '3999999993'];
        yield 'compact' => ['3999999993', '3999999993'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = HealthAndCareNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasksInThreeThreeFour(): void
    {
        $number = HealthAndCareNumber::from('3456789122');

        self::assertSame('345 678 9122', $number->formatted());
        self::assertSame('*** *** 9122', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $range = 'Invalid Northern Ireland Health and Care number (range).';

        yield 'just below the range' => ['320 000 0007', $range];
        yield 'just above the range' => ['400 000 0007', $range];
        yield 'England and Wales NHS number' => ['943 476 5919', $range];
        yield 'last digit changed' => ['320 000 0016', 'Invalid Northern Ireland Health and Care number checksum.'];
        yield 'nine digits' => ['320 000 001', 'Invalid Northern Ireland Health and Care number (9 characters).'];
        yield 'letter' => ['320 000 001X', 'Invalid Northern Ireland Health and Care number (invalid character).'];
        yield 'empty' => ['', 'Invalid Northern Ireland Health and Care number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(HealthAndCareNumber::from(...), $input, $message);
    }
}
