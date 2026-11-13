<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Document\AU\MedicareCardNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/AU.md "Medicare card number".
 */
#[CoversClass(MedicareCardNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
final class MedicareCardNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'worked example, issue 1' => ['3189 97702 1', '3189977021'];
        yield 'issue number 0' => ['3189977020', '3189977020'];
        yield 'first digit 2, check digit 0' => ['2123 45670 1', '2123456701'];
        yield 'first digit 6' => ['6000 00006 9', '6000000069'];
        yield 'hyphens, tabs and NBSP' => ["3189-977\t02\u{00A0}1", '3189977021'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = MedicareCardNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $number = MedicareCardNumber::from('3189977021');

        self::assertSame('3189 97702 1', $number->formatted());
        self::assertSame('**** ***02 1', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'check digit changed' => ['3189 97703 1', 'Invalid Medicare card number checksum.'];
        yield 'card digit changed' => ['3189 97802 1', 'Invalid Medicare card number checksum.'];
        yield 'first digit 1' => ['1189 97702 1', 'Invalid Medicare card number (first digit out of range).'];
        yield 'first digit 7' => ['7189 97702 1', 'Invalid Medicare card number (first digit out of range).'];
        yield 'first digit 0' => ['0000 00000 1', 'Invalid Medicare card number (first digit out of range).'];
        yield 'with the individual reference number' => ['3189 97702 1 1', 'Invalid Medicare card number (11 characters).'];
        yield 'nine digits' => ['3189 97702', 'Invalid Medicare card number (9 characters).'];
        yield 'letter' => ['3189 97702 I', 'Invalid Medicare card number (10 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): MedicareCardNumber => MedicareCardNumber::from($value), $input, $message);
    }
}
