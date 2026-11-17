<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CN;

use MSSTC4PHP\ValueObject\Documents\Document\CN\ExitEntryPermitToTaiwanNumber;
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
 * Expectations follow docs/sources/CN.md "Exit-entry permit to Taiwan".
 */
#[CoversClass(ExitEntryPermitToTaiwanNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class ExitEntryPermitToTaiwanNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'L and 8 digits' => ['L12345678', 'L12345678'];
        yield 'T and 8 digits (older permit)' => ['T12345678', 'T12345678'];
        yield 'lower case and separators' => ['l 12-345 678', 'L12345678'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = ExitEntryPermitToTaiwanNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*****5678', ExitEntryPermitToTaiwanNumber::from('L12345678')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $character = 'Invalid Chinese Taiwan exit-entry permit number (invalid character).';

        yield 'letter then letter' => ['LA1234567', $character];
        yield 'another prefix' => ['C12345678', $character];
        yield 'digit first' => ['112345678', $character];
        yield 'letter inside' => ['L1234A678', $character];
        yield 'eight characters' => ['L1234567', 'Invalid Chinese Taiwan exit-entry permit number (8 characters).'];
        yield 'ten characters' => ['L123456789', 'Invalid Chinese Taiwan exit-entry permit number (10 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): ExitEntryPermitToTaiwanNumber => ExitEntryPermitToTaiwanNumber::from($value),
            $input,
            $message,
        );
    }
}
