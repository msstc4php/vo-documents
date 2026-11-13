<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\FR;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\CH\PersonNameCodec as ChPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec as EsPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\AbstractFrenchNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\FR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\FR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonNameCodec as GbPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonNameCodec as TrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
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
#[UsesClass(ItPersonNameCodec::class)]
#[UsesClass(EsPersonNameCodec::class)]
#[UsesClass(NlPersonNameCodec::class)]
#[UsesClass(ChPersonNameCodec::class)]
#[UsesClass(GbPersonNameCodec::class)]
#[UsesClass(TrPersonNameCodec::class)]
#[UsesClass(PersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractFrenchNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
#[UsesClass(LatinSpelling::class)]
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
final class PersonNameCodecTest extends TestCase
{
    private const string SHAPE = 'Expected a French person name payload with string "family" and "given", '
        . 'string or null "latin", and no other keys.';

    public function testMapsItsClass(): void
    {
        self::assertSame(PersonName::class, new PersonNameCodec()->valueObjectClass());
    }

    public function testRoundTripWithoutOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('Dupont'), GivenName::from('Joël'));

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'Dupont', 'given' => 'Joël', 'latin' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Joël'),
            IcaoPersonName::from('MULLER<<JOEL'),
        );

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'Müller', 'given' => 'Joël', 'latin' => 'MULLER<<JOEL'], $payload);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('MULLER<<JOEL', $decoded->latinOverride()?->value);
    }

    public function testLowerCaseParticlesRoundTripUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('de La Fontaine'), GivenName::from('Jean-Pierre Marie'));

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'de La Fontaine', 'given' => 'Jean-Pierre Marie', 'latin' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
        self::assertSame('DE<LA<FONTAINE<<JEAN-PIERRE<MARIE', $codec->decode($payload)->latin()->value);
    }

    public function testDecodeAcceptsAMissingLatin(): void
    {
        self::assertNull(new PersonNameCodec()->decode(['family' => 'Dupont', 'given' => 'Zoé'])->latinOverride());
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'family is an int' => [['family' => 1, 'given' => 'Zoé']];
        yield 'family missing' => [['given' => 'Zoé']];
        yield 'given missing' => [['family' => 'Dupont']];
        yield 'given is an int' => [['family' => 'Dupont', 'given' => 1]];
        yield 'latin is an int' => [['family' => 'Dupont', 'given' => 'Zoé', 'latin' => 1]];
        yield 'unknown key' => [['family' => 'Dupont', 'given' => 'Zoé', 'latin' => null, 'patronymic' => null]];
        yield 'int key' => [['family' => 'Dupont', 'given' => 'Zoé', 0 => 'x']];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage(self::SHAPE);

        new PersonNameCodec()->decode($payload);
    }

    public function testTheJsonViewIsNotACodecPayload(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage(self::SHAPE);

        new PersonNameCodec()->decode(
            new PersonName(FamilyName::from('Dupont'), GivenName::from('Zoé'))->jsonSerialize(),
        );
    }

    public function testDecodeRefusesAnInvalidPart(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode(['family' => 'Иванов', 'given' => 'Zoé']);
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            PersonNameCodec::class . ' encodes ' . PersonName::class . ', ' . GivenName::class . ' given.',
        );

        new PersonNameCodec()->encode(GivenName::from('Zoé'));
    }

    public function testWorksThroughTheMapperFromUntrustedInput(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        $name = $mapper->fromUntrustedPayload(
            PersonName::class,
            ['family' => 'Müller', 'given' => 'Joël', 'latin' => 'muller<<joel'],
        );

        self::assertSame('MULLER<<JOEL', $name->latin()->value);
        $expected = ['family' => 'Müller', 'given' => 'Joël', 'latin' => 'MULLER<<JOEL'];
        self::assertSame($expected, $mapper->toPayload($name));
    }
}
