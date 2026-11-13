<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit;

use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NswDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonName as AuPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Codecs::class)]
#[UsesClass(PersonNameCodec::class)]
#[UsesClass(AuPersonNameCodec::class)]
final class CodecsTest extends TestCase
{
    public function testListsEveryCodecOfThePackage(): void
    {
        self::assertSame(
            [PersonName::class, AuPersonName::class],
            \array_map(static fn (PayloadCodec $codec): string => $codec->valueObjectClass(), Codecs::all()),
        );
    }

    public function testMakesTheMapperTreatCompositesAsComposites(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        self::assertTrue($mapper->isComposite(PersonName::class));
        self::assertTrue($mapper->supportsUntrusted(PersonName::class));
        self::assertTrue($mapper->isComposite(AuPersonName::class));
        self::assertTrue($mapper->supportsUntrusted(AuPersonName::class));
    }

    /**
     * Needs vo-base 1.1.1: 1.1.0 reported the interface as supported and failed on reading it.
     */
    public function testTheMapperSupportsTheConcreteLicenceClassNotTheInterface(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        self::assertFalse($mapper->supports(DriverLicenceNumber::class));
        self::assertFalse($mapper->supportsUntrusted(DriverLicenceNumber::class));
        self::assertTrue($mapper->supportsUntrusted(NswDriverLicenceNumber::class));
    }
}
