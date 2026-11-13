<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mask::class)]
final class MaskTest extends TestCase
{
    public function testKeepLastDigitsKeepsSeparatorsInPlace(): void
    {
        self::assertSame('***-***-*45 95', Mask::keepLastDigits('112-233-445 95', 4));
    }

    public function testZeroVisibleMasksEveryDigit(): void
    {
        self::assertSame('****', Mask::keepLastDigits('1234', 0));
    }

    public function testVisibleAtLeastDigitCountMasksNothing(): void
    {
        self::assertSame('12-34', Mask::keepLastDigits('12-34', 4));
        self::assertSame('12-34', Mask::keepLastDigits('12-34', 9));
    }

    public function testLettersAreKept(): void
    {
        self::assertSame('AB**-**56', Mask::keepLastDigits('AB12-3456', 2));
    }

    public function testKeepLastCharactersMasksLettersToo(): void
    {
        self::assertSame('*****9B7', Mask::keepLastCharacters('AB12C9B7', 3));
    }

    public function testKeepLastCharactersWithZeroOrNegativeVisibleMasksEverything(): void
    {
        self::assertSame('***', Mask::keepLastCharacters('A12', 0));
        self::assertSame('***', Mask::keepLastCharacters('A12', -1));
    }

    public function testKeepLastCharactersNeverShowsMoreThanTheValue(): void
    {
        self::assertSame('A12', Mask::keepLastCharacters('A12', 3));
        self::assertSame('A12', Mask::keepLastCharacters('A12', 5));
    }
}
