<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit;

use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Codecs::class)]
#[UsesClass(PersonNameCodec::class)]
final class CodecsTest extends TestCase
{
    public function testListsEveryCodecOfThePackage(): void
    {
        self::assertSame(
            [PersonName::class],
            \array_map(static fn (PayloadCodec $codec): string => $codec->valueObjectClass(), Codecs::all()),
        );
    }

    public function testMakesTheMapperTreatCompositesAsComposites(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        self::assertTrue($mapper->isComposite(PersonName::class));
        self::assertTrue($mapper->supportsUntrusted(PersonName::class));
    }
}
