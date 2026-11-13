<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use PHPUnit\Framework\Attributes\CoversClass;
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
}
