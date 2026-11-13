<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Digits::class)]
final class DigitsTest extends TestCase
{
    public function testStripRemovesDefaultSeparators(): void
    {
        self::assertSame('11223344595', Digits::strip("112 233-445\u{00A0}95"));
    }

    public function testStripRemovesTabs(): void
    {
        self::assertSame('123', Digits::strip("1\t2\t3"));
    }

    public function testStripKeepsLettersAndHonoursCustomSeparators(): void
    {
        self::assertSame('1a-2', Digits::strip('1a-2.', '.'));
    }
}
