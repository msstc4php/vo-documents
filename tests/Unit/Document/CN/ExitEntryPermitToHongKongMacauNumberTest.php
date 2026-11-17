<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CN;

use MSSTC4PHP\ValueObject\Documents\Document\CN\ExitEntryPermitToHongKongMacauNumber;
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
 * Expectations follow docs/sources/CN.md "Exit-entry permit to Hong Kong and Macao".
 */
#[CoversClass(ExitEntryPermitToHongKongMacauNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class ExitEntryPermitToHongKongMacauNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'C and 8 digits (2014 to 2025)' => ['C12345678', 'C12345678'];
        yield 'CA0000001, the first of the new range' => ['CA0000001', 'CA0000001'];
        yield 'letter H' => ['CH1234567', 'CH1234567'];
        yield 'letter J, after I' => ['CJ1234567', 'CJ1234567'];
        yield 'letter N' => ['CN1234567', 'CN1234567'];
        yield 'letter P, after O' => ['CP1234567', 'CP1234567'];
        yield 'letter Z' => ['CZ1234567', 'CZ1234567'];
        yield 'lower case and separators' => ['c 12-345 678', 'C12345678'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = ExitEntryPermitToHongKongMacauNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*****5678', ExitEntryPermitToHongKongMacauNumber::from('C12345678')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Chinese Hong Kong and Macao exit-entry permit number (invalid character).';

        yield 'letter I in the second place' => ['CI1234567', $character];
        yield 'letter O in the second place' => ['CO1234567', $character];
        yield 'letter in the third place' => ['CA123456A', $character];
        yield 'E prefix' => ['E12345678', $character];
        yield 'H prefix (the other direction)' => ['H12345678', $character];
        yield 'digit first' => ['112345678', $character];
        yield 'punctuation' => ['C1234567.', $character];
        yield 'eight characters' => ['C1234567', 'Invalid Chinese Hong Kong and Macao exit-entry permit number (8 characters).'];
        yield 'ten characters' => ['CA00000011', 'Invalid Chinese Hong Kong and Macao exit-entry permit number (10 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): ExitEntryPermitToHongKongMacauNumber => ExitEntryPermitToHongKongMacauNumber::from($value), $input, $message);
    }
}
