<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Document\GB\NhsCheckDigit;
use MSSTC4PHP\ValueObject\Documents\Document\GB\NhsNumber;
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
 * Expectations follow docs/sources/GB.md "NHS number"; the NHS Data Dictionary gives no example, so the numbers are
 * computed with its Modulus 11 steps.
 */
#[CoversClass(NhsNumber::class)]
#[CoversClass(NhsCheckDigit::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class NhsNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'display form' => ['943 476 5919', '9434765919'];
        yield 'compact' => ['4010232137', '4010232137'];
        yield 'eleven gives check digit 0' => ['100 000 0060', '1000000060'];
        yield 'remainder 0 gives check digit 1' => ['1000000001', '1000000001'];
        yield 'Northern Ireland range' => ['320 000 0015', '3200000015'];
        yield 'hyphens and NBSP' => ["943-476\u{00A0}5919", '9434765919'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = NhsNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsInThreeThreeFour(): void
    {
        self::assertSame('943 476 5919', NhsNumber::from('9434765919')->formatted());
    }

    public function testMasksInTheDisplayLayout(): void
    {
        self::assertSame('*** *** 5919', NhsNumber::from('9434765919')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $checksum = 'Invalid NHS number checksum.';

        yield 'last digit changed' => ['943 476 5918', $checksum];
        yield 'transposed digits' => ['943 476 9519', $checksum];
        yield 'eleven, but check digit 1' => ['100 000 0061', $checksum];
        yield 'ten is never issued (0)' => ['123 456 7890', $checksum];
        yield 'ten is never issued (1)' => ['123 456 7891', $checksum];
        yield 'nine digits' => ['943 476 591', 'Invalid NHS number (9 characters).'];
        yield 'eleven digits' => ['943 476 59190', 'Invalid NHS number (11 characters).'];
        yield 'letter' => ['943 476 591X', 'Invalid NHS number (invalid character).'];
        yield 'empty' => ['', 'Invalid NHS number (0 characters).'];
        yield 'far too long' => [\str_repeat('9', 50), 'Invalid NHS number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(NhsNumber::from(...), $input, $message);
    }
}
