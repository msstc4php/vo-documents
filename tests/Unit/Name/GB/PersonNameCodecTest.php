<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\GB;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CH\PersonNameCodec as ChPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec as EsPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\GB\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\GB\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonNameCodec as TrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonNameCodec::class)]
#[CoversClass(AbstractFamilyGivenPersonNameCodec::class)]
#[UsesClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractLatinPersonName::class)]
#[UsesClass(Codecs::class)]
#[UsesClass(RuPersonNameCodec::class)]
#[UsesClass(AuPersonNameCodec::class)]
#[UsesClass(DePersonNameCodec::class)]
#[UsesClass(FrPersonNameCodec::class)]
#[UsesClass(ItPersonNameCodec::class)]
#[UsesClass(EsPersonNameCodec::class)]
#[UsesClass(NlPersonNameCodec::class)]
#[UsesClass(ChPersonNameCodec::class)]
#[UsesClass(TrPersonNameCodec::class)]
#[UsesClass(PersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(AnyIcaoSpelling::class)]
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
final class PersonNameCodecTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string SHAPE = 'Expected a British person name payload with string "family", '
        . 'string or null "given" and "latin", and no other keys.';

    public function testMapsItsClass(): void
    {
        self::assertSame(PersonName::class, new PersonNameCodec()->valueObjectClass());
    }

    public function testRoundTripsAnApostropheName(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from("O'Brien"), GivenName::from('Siân'));

        $payload = $codec->encode($name);

        self::assertSame(['family' => "O'Brien", 'given' => 'Siân', 'latin' => null], $payload);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('OBRIEN<<SIAN', $decoded->latin()->value);
    }

    public function testWelshParticleAndDoubleBarrelRoundTripUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('ap Rhys-Llŷr'), GivenName::from('Mary-Kate'));

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'ap Rhys-Llŷr', 'given' => 'Mary-Kate', 'latin' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Anna'),
            IcaoPersonName::from('MILLER<<ANNA'),
        );

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'Müller', 'given' => 'Anna', 'latin' => 'MILLER<<ANNA'], $payload);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('MILLER<<ANNA', $decoded->latinOverride()?->value);
    }

    public function testRoundTripWithoutAGivenName(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('Müller'), null);

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'Müller', 'given' => null, 'latin' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testDecodeAcceptsMissingOptionalKeys(): void
    {
        $name = new PersonNameCodec()->decode(['family' => 'Müller']);

        self::assertNull($name->given);
        self::assertNull($name->latinOverride());
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'family is an int' => [['family' => 1, 'given' => 'Anna']];
        yield 'family missing' => [['given' => 'Anna']];
        yield 'family is null' => [['family' => null, 'given' => 'Anna']];
        yield 'given is an int' => [['family' => 'Berg', 'given' => 1]];
        yield 'latin is an int' => [['family' => 'Berg', 'given' => 'Anna', 'latin' => 1]];
        yield 'unknown key' => [['family' => 'Berg', 'given' => 'Anna', 'latin' => null, 'middle' => 'x']];
        yield 'int key' => [['family' => 'Berg', 'given' => 'Anna', 0 => 'x']];
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
                new PersonName(FamilyName::from('Berg'), GivenName::from('Anna'))->jsonSerialize(),
            ),
            '',
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    public function testDecodeRefusesAnInvalidPart(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode(['family' => 'Смит', 'given' => 'Anna']);
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): array => new PersonNameCodec()->encode(GivenName::from('Anna')),
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
            ['family' => 'Müller', 'given' => 'Anna Maria', 'latin' => 'MULLER<<ANNA<MARIA'],
        );

        self::assertSame('MULLER<<ANNA<MARIA', $name->latin()->value);
        $expected = ['family' => 'Müller', 'given' => 'Anna Maria', 'latin' => 'MULLER<<ANNA<MARIA'];
        self::assertSame($expected, $mapper->toPayload($name));
    }
}
