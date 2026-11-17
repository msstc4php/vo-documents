<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\MX;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\BR\PersonNameCodec as BrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CA\PersonNameCodec as CaPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CH\PersonNameCodec as ChPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CN\PersonNameCodec as CnPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec as EsPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonNameCodec as GbPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ID\PersonNameCodec as IdPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\PersonNameCodec as InDevanagariPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IN\PersonNameCodec as InPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PersonNameCodec as JpPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\KR\PersonNameCodec as KrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\MX\AbstractMexicanNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\MX\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\MX\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonNameCodec as TrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Name\US\PersonNameCodec as UsPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonNameCodec::class)]
#[UsesClass(Codecs::class)]
#[UsesClass(EsPersonNameCodec::class)]
#[UsesClass(CaPersonNameCodec::class)]
#[UsesClass(BrPersonNameCodec::class)]
#[UsesClass(CnPersonNameCodec::class)]
#[UsesClass(JpPersonNameCodec::class)]
#[UsesClass(KrPersonNameCodec::class)]
#[UsesClass(InPersonNameCodec::class)]
#[UsesClass(InDevanagariPersonNameCodec::class)]
#[UsesClass(IdPersonNameCodec::class)]
#[UsesClass(RuPersonNameCodec::class)]
#[UsesClass(AuPersonNameCodec::class)]
#[UsesClass(DePersonNameCodec::class)]
#[UsesClass(FrPersonNameCodec::class)]
#[UsesClass(ItPersonNameCodec::class)]
#[UsesClass(NlPersonNameCodec::class)]
#[UsesClass(ChPersonNameCodec::class)]
#[UsesClass(GbPersonNameCodec::class)]
#[UsesClass(TrPersonNameCodec::class)]
#[UsesClass(UsPersonNameCodec::class)]
#[UsesClass(PersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractMexicanNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(IdentifierSpellings::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(TransliterationVariant::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(PayloadFields::class)]
#[UsesClass(Optional::class)]
#[UsesClass(Text::class)]
#[UsesClass(LatinForm::class)]
final class PersonNameCodecTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string SHAPE = 'Expected a Mexican person name payload with string "given", '
        . 'string, null or missing "first", "second" and "latin", and no other keys.';

    public function testMapsItsClass(): void
    {
        self::assertSame(PersonName::class, new PersonNameCodec()->valueObjectClass());
    }

    public function testRoundTripWithTwoSurnamesAndParticlesUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('Hernández'), FamilyName::from('de la Cruz'), GivenName::from('José'));

        $payload = $codec->encode($name);

        self::assertSame(
            ['first' => 'Hernández', 'second' => 'de la Cruz', 'given' => 'José', 'latin' => null],
            $payload,
        );
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('de la Cruz', $decoded->second?->value);
    }

    public function testRoundTripWithOneSurnameUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('de la Cruz'), null, GivenName::from('Pilar'));

        $payload = $codec->encode($name);

        self::assertSame(['first' => 'de la Cruz', 'second' => null, 'given' => 'Pilar', 'latin' => null], $payload);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertNull($decoded->second);
        self::assertSame('DE<LA<CRUZ<<PILAR', $decoded->latin()->value);
    }

    public function testRoundTripWithoutSurnames(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(null, null, GivenName::from('Nancy'));

        $payload = $codec->encode($name);

        self::assertSame(['first' => null, 'second' => null, 'given' => 'Nancy', 'latin' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithRenapoPunctuationUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            FamilyName::from('D/Amico'),
            FamilyName::from('Álvarez'),
            GivenName::from('Ma. Guadalupe'),
        );

        $payload = $codec->encode($name);

        self::assertSame(
            ['first' => 'D/Amico', 'second' => 'Álvarez', 'given' => 'Ma. Guadalupe', 'latin' => null],
            $payload,
        );
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('DAMICO<ALVAREZ<<MA<GUADALUPE', $decoded->latin()->value);
    }

    public function testRoundTripWithOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            FamilyName::from('Argüello'),
            null,
            GivenName::from('Ana'),
            IcaoPersonName::from('ARGUELLO<<ANA'),
        );

        $payload = $codec->encode($name);

        self::assertSame(
            ['first' => 'Argüello', 'second' => null, 'given' => 'Ana', 'latin' => 'ARGUELLO<<ANA'],
            $payload,
        );
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('ARGUELLO<<ANA', $decoded->latinOverride()?->value);
    }

    public function testDecodeAcceptsMissingNullableKeys(): void
    {
        $name = new PersonNameCodec()->decode(['given' => 'Ana']);

        self::assertNull($name->first);
        self::assertNull($name->second);
        self::assertNull($name->latinOverride());
        self::assertNull(new PersonNameCodec()->decode(['first' => 'Zaro', 'given' => 'Ana'])->second);
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'first is an int' => [['first' => 1, 'given' => 'Ana']];
        yield 'given missing' => [['first' => 'García']];
        yield 'given is null' => [['first' => 'García', 'given' => null]];
        yield 'second is an int' => [['first' => 'García', 'second' => 1, 'given' => 'Ana']];
        yield 'latin is an int' => [['first' => 'García', 'given' => 'Ana', 'latin' => 1]];
        yield 'family key of other countries' => [['family' => 'García', 'given' => 'Ana']];
        yield 'int key' => [['first' => 'García', 'given' => 'Ana', 0 => 'x']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonNameCodec()->decode($payload),
            '',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    public function testTheJsonViewIsNotACodecPayload(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonNameCodec()->decode(
                new PersonName(FamilyName::from('García'), null, GivenName::from('Ana'))->jsonSerialize(),
            ),
            '',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    public function testDecodeRefusesInvalidParts(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonNameCodec()->decode(
                ['first' => 'García', 'second' => 'Лопес', 'given' => 'Ana'],
            ),
            '',
            'Invalid Mexican family name (invalid character).',
        );
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonNameCodec()->decode(['first' => 'Лопес', 'given' => 'Ana']),
            '',
            'Invalid Mexican family name (invalid character).',
        );
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonNameCodec()->decode(['second' => 'García', 'given' => 'Ana']),
            '',
            'Invalid Mexican person name (second surname without a first surname).',
        );
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): array => new PersonNameCodec()->encode(GivenName::from('Ana')),
            '',
            PersonNameCodec::class . ' encodes ' . PersonName::class . ', ' . GivenName::class . ' given.',
            LogicException::class,
        );
    }

    public function testWorksThroughTheMapperFromUntrustedInput(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        $name = $mapper->fromUntrustedPayload(
            PersonName::class,
            ['first' => 'Núñez', 'second' => null, 'given' => 'Ana', 'latin' => 'nunxxez<<ana'],
        );

        self::assertSame('NUNXXEZ<<ANA', $name->latin()->value);
        $expected = ['first' => 'Núñez', 'second' => null, 'given' => 'Ana', 'latin' => 'NUNXXEZ<<ANA'];
        self::assertSame($expected, $mapper->toPayload($name));
    }
}
