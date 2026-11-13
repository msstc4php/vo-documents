<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Text::class)]
final class TextTest extends TestCase
{
    public function testNonEmptyReturnsTheValue(): void
    {
        self::assertSame('AB1', Text::nonEmpty('AB1'));
    }

    public function testNonEmptyRefusesTheEmptyString(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A stored value is never empty.');

        Text::nonEmpty('');
    }
}
