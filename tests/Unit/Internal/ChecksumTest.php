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

    public function testMod97ReturnsRemainder(): void
    {
        self::assertSame(1, Checksum::mod97('3214282912345698765432161182'));
        self::assertSame(0, Checksum::mod97('97'));
    }

    public function testIso7064Mod112ReturnsCheckCharacter(): void
    {
        self::assertSame('X', Checksum::iso7064Mod11_2('11010519491231002'));
        self::assertSame('0', Checksum::iso7064Mod11_2('0794'));
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
        yield 'mod97 space' => [static fn (): int => Checksum::mod97('12 3')];
        yield 'mod97 trailing newline' => [static fn (): int => Checksum::mod97("123\n")];
        yield 'mod11-2 X' => [static fn (): string => Checksum::iso7064Mod11_2('12X')];
        yield 'luhn empty' => [static fn (): bool => Checksum::luhn('')];
        yield 'weightedMod empty' => [static fn (): int => Checksum::weightedMod('', [], 11)];
        yield 'mod97 empty' => [static fn (): int => Checksum::mod97('')];
        yield 'mod11-2 empty' => [static fn (): string => Checksum::iso7064Mod11_2('')];
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
