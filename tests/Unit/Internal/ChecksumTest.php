<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use Closure;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Checksum::class)]
final class ChecksumTest extends TestCase
{
    public function testLuhnAcceptsValidNumber(): void
    {
        self::assertTrue(Checksum::luhn('79927398713'));
    }

    public function testLuhnRejectsInvalidNumber(): void
    {
        self::assertFalse(Checksum::luhn('79927398710'));
    }

    public function testWeightedModSumsDigitTimesWeight(): void
    {
        self::assertSame(95, Checksum::weightedMod('112233445', [9, 8, 7, 6, 5, 4, 3, 2, 1], 101));
    }

    public function testIso7064Mod11Dash2ReturnsCheckCharacter(): void
    {
        self::assertSame('X', Checksum::iso7064Mod11Dash2('11010519491231002'));
        self::assertSame('0', Checksum::iso7064Mod11Dash2('0794'));
    }

    /**
     * @return iterable<string, array{Closure(): mixed}>
     */
    public static function invalidCalls(): iterable
    {
        yield 'luhn letters' => [static fn (): bool => Checksum::luhn('12a4')];
        yield 'weightedMod letters' => [static fn (): int => Checksum::weightedMod('1a', [1, 2], 11)];
        yield 'weightedMod short' => [static fn (): int => Checksum::weightedMod('12', [1, 2, 3], 11)];
        yield 'weightedMod long' => [static fn (): int => Checksum::weightedMod('123', [1, 2], 11)];
        yield 'luhn space' => [static fn (): bool => Checksum::luhn('12 3')];
        yield 'luhn trailing newline' => [static fn (): bool => Checksum::luhn("123\n")];
        yield 'mod11-2 X' => [static fn (): string => Checksum::iso7064Mod11Dash2('12X')];
        yield 'luhn empty' => [static fn (): bool => Checksum::luhn('')];
        yield 'weightedMod empty' => [static fn (): int => Checksum::weightedMod('', [], 11)];
        yield 'mod11-2 empty' => [static fn (): string => Checksum::iso7064Mod11Dash2('')];
    }

    /**
     * @param Closure(): mixed $call
     */
    #[DataProvider('invalidCalls')]
    public function testNonDigitOrMismatchedInputThrowsLogicException(Closure $call): void
    {
        $this->expectException(LogicException::class);

        $call();
    }
}
