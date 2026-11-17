<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CN;

use MSSTC4PHP\ValueObject\Documents\Document\CN\OrdinaryPassportNumber;
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
 * Expectations follow docs/sources/CN.md "Ordinary passport number".
 */
#[CoversClass(OrdinaryPassportNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class OrdinaryPassportNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'E and 8 digits (before April 2017)' => ['E12345678', 'E12345678'];
        yield 'EA0000001, the first of the new range' => ['EA0000001', 'EA0000001'];
        yield 'letter H' => ['EH1234567', 'EH1234567'];
        yield 'letter J, after I' => ['EJ1234567', 'EJ1234567'];
        yield 'letter N' => ['EN1234567', 'EN1234567'];
        yield 'letter P, after O' => ['EP1234567', 'EP1234567'];
        yield 'letter Z' => ['EZ1234567', 'EZ1234567'];
        yield 'G and 8 digits (paper passport)' => ['G12345678', 'G12345678'];
        yield 'lower case, separators, NBSP' => ["ea 000-0001\u{00A0}", 'EA0000001'];
        yield 'fullwidth' => ["\u{FF25}\u{FF21}\u{FF10}\u{FF10}\u{FF10}\u{FF10}\u{FF10}\u{FF10}\u{FF11}", 'EA0000001'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = OrdinaryPassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*****0001', OrdinaryPassportNumber::from('EA0000001')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Chinese ordinary passport number (invalid character).';

        yield 'letter I in the second place' => ['EI1234567', $character];
        yield 'letter O in the second place' => ['EO1234567', $character];
        yield 'letter in the third place' => ['EA123456A', $character];
        yield 'two letters then 6 digits and a letter' => ['EAB123456', $character];
        yield 'another series letter' => ['P12345678', $character];
        yield 'S series' => ['S12345678', $character];
        yield 'digit first' => ['112345678', $character];
        yield 'G with a letter second' => ['GA1234567', $character];
        yield 'punctuation' => ['E1234567.', $character];
        yield 'eight characters' => ['E1234567', 'Invalid Chinese ordinary passport number (8 characters).'];
        yield 'ten characters' => ['EA00000011', 'Invalid Chinese ordinary passport number (10 characters).'];
        yield 'far too long' => [\str_repeat('E', 41), 'Invalid Chinese ordinary passport number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): OrdinaryPassportNumber => OrdinaryPassportNumber::from($value),
            $input,
            $message,
        );
    }
}
