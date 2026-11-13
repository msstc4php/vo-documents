<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PayloadFields::class)]
final class PayloadFieldsTest extends TestCase
{
    public function testAcceptsKnownKeysAndReadsStrings(): void
    {
        $payload = ['a' => 'x', 'b' => null];

        PayloadFields::assertOnlyKeys($payload, ['a', 'b', 'c'], 'Shape.');

        self::assertSame('x', PayloadFields::string($payload, 'a', 'Shape.'));
        self::assertNull(PayloadFields::nullableString($payload, 'b', 'Shape.'));
        self::assertNull(PayloadFields::nullableString($payload, 'c', 'Shape.'));
        self::assertSame('x', PayloadFields::nullableString($payload, 'a', 'Shape.'));
    }

    public function testRefusesAnUnknownKey(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::assertOnlyKeys(['a' => 'x', 0 => 'y'], ['a'], 'Shape.');
    }

    public function testRefusesAMissingString(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::string([], 'a', 'Shape.');
    }

    public function testRefusesANonString(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::string(['a' => 1], 'a', 'Shape.');
    }

    public function testRefusesANonStringNullable(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Shape.');

        PayloadFields::nullableString(['a' => 1], 'a', 'Shape.');
    }
}
