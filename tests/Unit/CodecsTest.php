<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit;

use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NswDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonName as AuPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\BR\PersonName as BrPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\BR\PersonNameCodec as BrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CA\PersonName as CaPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\CA\PersonNameCodec as CaPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CH\PersonName as ChPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\CH\PersonNameCodec as ChPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CN\PersonName as CnPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\CN\PersonNameCodec as CnPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonName as DePersonName;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonName as EsPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec as EsPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonName as FrPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonName as GbPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonNameCodec as GbPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ID\PersonName as IdPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\ID\PersonNameCodec as IdPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\PersonName as InDevanagariPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\PersonNameCodec as InDevanagariPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IN\PersonName as InPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\PersonNameCodec as InPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonName as ItPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PersonName as JpPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PersonNameCodec as JpPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\KR\PersonName as KrPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\KR\PersonNameCodec as KrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonName as MxPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonNameCodec as MxPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonName as NlPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\SA\PersonName as SaPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\SA\PersonNameCodec as SaPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonName as TrPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonNameCodec as TrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\US\PersonName as UsPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\US\PersonNameCodec as UsPersonNameCodec;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use MSSTC4PHP\ValueObject\Payload\ProvidesCodecs;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Codecs::class)]
#[UsesClass(PersonNameCodec::class)]
#[UsesClass(AuPersonNameCodec::class)]
#[UsesClass(DePersonNameCodec::class)]
#[UsesClass(FrPersonNameCodec::class)]
#[UsesClass(ItPersonNameCodec::class)]
#[UsesClass(EsPersonNameCodec::class)]
#[UsesClass(NlPersonNameCodec::class)]
#[UsesClass(ChPersonNameCodec::class)]
#[UsesClass(GbPersonNameCodec::class)]
#[UsesClass(TrPersonNameCodec::class)]
#[UsesClass(UsPersonNameCodec::class)]
#[UsesClass(CaPersonNameCodec::class)]
#[UsesClass(BrPersonNameCodec::class)]
#[UsesClass(MxPersonNameCodec::class)]
#[UsesClass(CnPersonNameCodec::class)]
#[UsesClass(JpPersonNameCodec::class)]
#[UsesClass(KrPersonNameCodec::class)]
#[UsesClass(InPersonNameCodec::class)]
#[UsesClass(InDevanagariPersonNameCodec::class)]
#[UsesClass(IdPersonNameCodec::class)]
#[UsesClass(SaPersonNameCodec::class)]
final class CodecsTest extends TestCase
{
    public function testListsEveryCodecOfThePackage(): void
    {
        self::assertSame(
            [
                PersonName::class,
                AuPersonName::class,
                DePersonName::class,
                FrPersonName::class,
                ItPersonName::class,
                EsPersonName::class,
                NlPersonName::class,
                ChPersonName::class,
                GbPersonName::class,
                TrPersonName::class,
                UsPersonName::class,
                CaPersonName::class,
                MxPersonName::class,
                BrPersonName::class,
                CnPersonName::class,
                JpPersonName::class,
                KrPersonName::class,
                InPersonName::class,
                InDevanagariPersonName::class,
                IdPersonName::class,
                SaPersonName::class,
            ],
            \array_map(static fn (PayloadCodec $codec): string => $codec->valueObjectClass(), Codecs::all()),
        );
    }

    public function testIsTheCodecProviderOfThePackage(): void
    {
        self::assertContains(ProvidesCodecs::class, \class_implements(Codecs::class));
    }

    public function testMakesTheMapperTreatCompositesAsComposites(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        self::assertTrue($mapper->isComposite(PersonName::class));
        self::assertTrue($mapper->supportsUntrusted(PersonName::class));
        self::assertTrue($mapper->isComposite(AuPersonName::class));
        self::assertTrue($mapper->supportsUntrusted(AuPersonName::class));
        self::assertTrue($mapper->isComposite(DePersonName::class));
        self::assertTrue($mapper->isComposite(FrPersonName::class));
        self::assertTrue($mapper->isComposite(ItPersonName::class));
        self::assertTrue($mapper->isComposite(EsPersonName::class));
        self::assertTrue($mapper->isComposite(NlPersonName::class));
        self::assertTrue($mapper->isComposite(ChPersonName::class));
        self::assertTrue($mapper->isComposite(GbPersonName::class));
        self::assertTrue($mapper->isComposite(TrPersonName::class));
        self::assertTrue($mapper->isComposite(UsPersonName::class));
        self::assertTrue($mapper->isComposite(CaPersonName::class));
        self::assertTrue($mapper->isComposite(MxPersonName::class));
        self::assertTrue($mapper->isComposite(BrPersonName::class));
        self::assertTrue($mapper->isComposite(CnPersonName::class));
        self::assertTrue($mapper->isComposite(JpPersonName::class));
        self::assertTrue($mapper->isComposite(KrPersonName::class));
        self::assertTrue($mapper->isComposite(InPersonName::class));
        self::assertTrue($mapper->isComposite(InDevanagariPersonName::class));
        self::assertTrue($mapper->isComposite(IdPersonName::class));
        self::assertTrue($mapper->isComposite(SaPersonName::class));
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
