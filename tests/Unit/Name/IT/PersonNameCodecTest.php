<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\IT;

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
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec as EsPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\IT\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec as RuPersonNameCodec;
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
#[UsesClass(FrPersonNameCodec::class)]
#[UsesClass(EsPersonNameCodec::class)]
#[UsesClass(PersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
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
    private const string SHAPE = 'Expected an Italian person name payload with string "family" and "given", '
        . 'string or null "latin", and no other keys.';

    public function testMapsItsClass(): void
    {
        self::assertSame(PersonName::class, new PersonNameCodec()->valueObjectClass());
    }

    public function testRoundTripWithoutOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('Rossi'), GivenName::from('Nicolò'));

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'Rossi', 'given' => 'Nicolò', 'latin' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Anna'),
            IcaoPersonName::from('MULLER<<ANNA'),
        );

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'Müller', 'given' => 'Anna', 'latin' => 'MULLER<<ANNA'], $payload);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('MULLER<<ANNA', $decoded->latinOverride()?->value);
    }

    public function testLowerCaseParticlesRoundTripUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('di Pietro'), GivenName::from('Maria Grazia'));

        $payload = $codec->encode($name);

        self::assertSame(['family' => 'di Pietro', 'given' => 'Maria Grazia', 'latin' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
        self::assertSame('DI<PIETRO<<MARIA<GRAZIA', $codec->decode($payload)->latin()->value);
    }

    public function testDecodeAcceptsAMissingLatin(): void
    {
        self::assertNull(new PersonNameCodec()->decode(['family' => 'Rossi', 'given' => 'Giulia'])->latinOverride());
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'family is an int' => [['family' => 1, 'given' => 'Giulia']];
        yield 'family missing' => [['given' => 'Giulia']];
        yield 'given missing' => [['family' => 'Rossi']];
        yield 'given is an int' => [['family' => 'Rossi', 'given' => 1]];
        yield 'latin is an int' => [['family' => 'Rossi', 'given' => 'Giulia', 'latin' => 1]];
        yield 'unknown key' => [['family' => 'Rossi', 'given' => 'Giulia', 'latin' => null, 'patronymic' => null]];
        yield 'int key' => [['family' => 'Rossi', 'given' => 'Giulia', 0 => 'x']];
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
            new PersonName(FamilyName::from('Rossi'), GivenName::from('Giulia'))->jsonSerialize(),
        );
    }

    public function testDecodeRefusesAnInvalidPart(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode(['family' => 'Иванов', 'given' => 'Giulia']);
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            PersonNameCodec::class . ' encodes ' . PersonName::class . ', ' . GivenName::class . ' given.',
        );

        new PersonNameCodec()->encode(GivenName::from('Giulia'));
    }

    public function testWorksThroughTheMapperFromUntrustedInput(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        $name = $mapper->fromUntrustedPayload(
            PersonName::class,
            ['family' => 'Müller', 'given' => 'Anna', 'latin' => 'muller<<anna'],
        );

        self::assertSame('MULLER<<ANNA', $name->latin()->value);
        $expected = ['family' => 'Müller', 'given' => 'Anna', 'latin' => 'MULLER<<ANNA'];
        self::assertSame($expected, $mapper->toPayload($name));
    }
}
