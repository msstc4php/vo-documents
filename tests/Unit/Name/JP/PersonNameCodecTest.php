<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\JP;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
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
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\PersonNameCodec as InDevanagariPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IN\PersonNameCodec as InPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\JP\AbstractJapaneseNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\JP\AbstractJapaneseReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\FamilyNameReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\GivenNameReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PassportHepburn;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\KR\PersonNameCodec as KrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonNameCodec as MxPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonNameCodec as TrPersonNameCodec;
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
#[UsesClass(RuPersonNameCodec::class)]
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
#[UsesClass(MxPersonNameCodec::class)]
#[UsesClass(BrPersonNameCodec::class)]
#[UsesClass(CnPersonNameCodec::class)]
#[UsesClass(KrPersonNameCodec::class)]
#[UsesClass(InPersonNameCodec::class)]
#[UsesClass(InDevanagariPersonNameCodec::class)]
#[UsesClass(IdPersonNameCodec::class)]
#[UsesClass(PersonName::class)]
#[UsesClass(PassportHepburn::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(FamilyNameReading::class)]
#[UsesClass(GivenNameReading::class)]
#[UsesClass(AbstractJapaneseNamePart::class)]
#[UsesClass(AbstractJapaneseReading::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(AnyIcaoSpelling::class)]
#[UsesClass(LatinForm::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(PayloadFields::class)]
#[UsesClass(Optional::class)]
final class PersonNameCodecTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string SHAPE = 'Expected a Japanese person name payload with string "family", "given", '
        . '"familyReading" and "givenReading", string, null or missing "latin", and no other keys.';

    public function testMapsItsClass(): void
    {
        self::assertSame(PersonName::class, new PersonNameCodec()->valueObjectClass());
    }

    public function testRoundTripWithoutOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = self::japaneseName();

        $payload = $codec->encode($name);

        self::assertSame(
            ['family' => '大野', 'given' => '翔', 'familyReading' => 'オオノ', 'givenReading' => 'しょう', 'latin' => null],
            $payload,
        );
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = self::japaneseName(IcaoPersonName::from('OHNO<<SHO'));

        $payload = $codec->encode($name);

        self::assertSame('OHNO<<SHO', $payload['latin']);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('OHNO<<SHO', $decoded->latinOverride()?->value);
    }

    public function testAnOverrideThatIsTheDerivedFormIsStoredAsNull(): void
    {
        self::assertNull(new PersonNameCodec()->encode(self::japaneseName(IcaoPersonName::from('ONO<<SHO')))['latin']);
    }

    public function testDecodeAcceptsAMissingLatin(): void
    {
        $name = new PersonNameCodec()->decode(
            ['family' => '大野', 'given' => '翔', 'familyReading' => 'オオノ', 'givenReading' => 'しょう'],
        );

        self::assertNull($name->latinOverride());
        self::assertSame('ONO<<SHO', $name->latin()->value);
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        $valid = ['family' => '大野', 'given' => '翔', 'familyReading' => 'オオノ', 'givenReading' => 'しょう'];

        yield 'family missing' => [\array_diff_key($valid, ['family' => true])];
        yield 'given missing' => [\array_diff_key($valid, ['given' => true])];
        yield 'familyReading missing' => [\array_diff_key($valid, ['familyReading' => true])];
        yield 'givenReading missing' => [\array_diff_key($valid, ['givenReading' => true])];
        yield 'family is an int' => [['family' => 1] + $valid];
        yield 'givenReading is null' => [['givenReading' => null] + $valid];
        yield 'latin is an int' => [$valid + ['latin' => 1]];
        yield 'unknown key' => [$valid + ['latin' => null, 'extra' => 'x']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            '',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    public function testTheJsonViewIsNotACodecPayload(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode(self::japaneseName()->jsonSerialize()),
            '',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    public function testDecodeRefusesAnInvalidReading(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode(
            ['family' => '大野', 'given' => '翔', 'familyReading' => '大野', 'givenReading' => 'しょう', 'latin' => null],
        );
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): array => new PersonNameCodec()->encode(GivenName::from('翔')),
            '',
            PersonNameCodec::class . ' encodes ' . PersonName::class . ', ' . GivenName::class . ' given.',
            LogicException::class,
        );
    }

    public function testWorksThroughTheMapperFromUntrustedInput(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());
        $payload = [
            'family' => '伊藤',
            'given' => '洋子',
            'familyReading' => 'イトウ',
            'givenReading' => 'ヨウコ',
            'latin' => 'itoh<<yoko',
        ];

        $name = $mapper->fromUntrustedPayload(PersonName::class, $payload);

        self::assertSame('ITOH<<YOKO', $name->latin()->value);
        self::assertSame(\array_replace($payload, ['latin' => 'ITOH<<YOKO']), $mapper->toPayload($name));
    }

    private static function japaneseName(?IcaoPersonName $latin = null): PersonName
    {
        return new PersonName(
            FamilyName::from('大野'),
            GivenName::from('翔'),
            FamilyNameReading::from('オオノ'),
            GivenNameReading::from('しょう'),
            $latin,
        );
    }
}
