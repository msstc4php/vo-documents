<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\ES;

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
use MSSTC4PHP\ValueObject\Documents\Name\ES\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec;
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
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonNameCodec as MxPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\SA\PersonNameCodec as SaPersonNameCodec;
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
#[UsesClass(MxPersonNameCodec::class)]
#[UsesClass(BrPersonNameCodec::class)]
#[UsesClass(CnPersonNameCodec::class)]
#[UsesClass(JpPersonNameCodec::class)]
#[UsesClass(KrPersonNameCodec::class)]
#[UsesClass(InPersonNameCodec::class)]
#[UsesClass(InDevanagariPersonNameCodec::class)]
#[UsesClass(IdPersonNameCodec::class)]
#[UsesClass(SaPersonNameCodec::class)]
#[UsesClass(CaPersonNameCodec::class)]
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

    private const string SHAPE = 'Expected a Spanish person name payload with string "firstSurname" and "given", '
        . 'string, null or missing "secondSurname" and "latinOverride", and no other keys.';

    public function testMapsItsClass(): void
    {
        self::assertSame(PersonName::class, new PersonNameCodec()->valueObjectClass());
    }

    public function testRoundTripWithTwoSurnames(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('García'), FamilyName::from('López'), GivenName::from('María José'));

        $payload = $codec->encode($name);

        self::assertSame(
            ['firstSurname' => 'García', 'secondSurname' => 'López', 'given' => 'María José', 'latinOverride' => null],
            $payload,
        );
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithOneSurnameUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('de la Fuente'), null, GivenName::from('Pilar'));

        $payload = $codec->encode($name);

        self::assertSame(['firstSurname' => 'de la Fuente', 'secondSurname' => null, 'given' => 'Pilar', 'latinOverride' => null], $payload);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertNull($decoded->secondSurname);
        self::assertSame('DE<LA<FUENTE<<PILAR', $decoded->latin()->value);
    }

    public function testRoundTripWithOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            FamilyName::from('Argüelles'),
            null,
            GivenName::from('Ana'),
            IcaoPersonName::from('ARGUELLES<<ANA'),
        );

        $payload = $codec->encode($name);

        self::assertSame(
            ['firstSurname' => 'Argüelles', 'secondSurname' => null, 'given' => 'Ana', 'latinOverride' => 'ARGUELLES<<ANA'],
            $payload,
        );
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('ARGUELLES<<ANA', $decoded->latinOverride()?->value);
    }

    public function testDecodeAcceptsMissingSecondAndLatin(): void
    {
        $name = new PersonNameCodec()->decode(['firstSurname' => 'García', 'given' => 'Ana']);

        self::assertNull($name->secondSurname);
        self::assertNull($name->latinOverride());
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'first is an int' => [['firstSurname' => 1, 'given' => 'Ana']];
        yield 'first missing' => [['given' => 'Ana']];
        yield 'given missing' => [['firstSurname' => 'García']];
        yield 'given is an int' => [['firstSurname' => 'García', 'given' => 1]];
        yield 'second is an int' => [['firstSurname' => 'García', 'secondSurname' => 1, 'given' => 'Ana']];
        yield 'latin is an int' => [['firstSurname' => 'García', 'given' => 'Ana', 'latinOverride' => 1]];
        yield 'family key of other countries' => [['family' => 'García', 'given' => 'Ana']];
        yield 'int key' => [['firstSurname' => 'García', 'given' => 'Ana', 0 => 'x']];
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

    public function testTheJsonViewReadsBackAsTheSameName(): void
    {
        $name = new PersonName(FamilyName::from('García'), null, GivenName::from('Ana'));

        self::assertTrue(new PersonNameCodec()->decode($name->jsonSerialize())->equals($name));
    }

    public function testDecodeRefusesAnInvalidSecondSurname(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonNameCodec()->decode(
                ['firstSurname' => 'García', 'secondSurname' => 'Лопес', 'given' => 'Ana'],
            ),
            '',
            'Invalid Spanish family name (invalid character).',
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
            ['firstSurname' => 'Muñoz', 'secondSurname' => null, 'given' => 'Ana', 'latinOverride' => 'munxxoz<<ana'],
        );

        self::assertSame('MUNXXOZ<<ANA', $name->latin()->value);
        $expected = ['firstSurname' => 'Muñoz', 'secondSurname' => null, 'given' => 'Ana', 'latinOverride' => 'MUNXXOZ<<ANA'];
        self::assertSame($expected, $mapper->toPayload($name));
    }
}
