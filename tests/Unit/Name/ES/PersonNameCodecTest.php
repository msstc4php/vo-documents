<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\ES;

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
use MSSTC4PHP\ValueObject\Documents\Name\ES\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonNameCodec as NlPersonNameCodec;
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
#[UsesClass(ItPersonNameCodec::class)]
#[UsesClass(NlPersonNameCodec::class)]
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
    private const string SHAPE = 'Expected a Spanish person name payload with string "first" and "given", '
        . 'string or null "second" and "latin", and no other keys.';

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
            ['first' => 'García', 'second' => 'López', 'given' => 'María José', 'latin' => null],
            $payload,
        );
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithOneSurnameUnchanged(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('de la Fuente'), null, GivenName::from('Pilar'));

        $payload = $codec->encode($name);

        self::assertSame(['first' => 'de la Fuente', 'second' => null, 'given' => 'Pilar', 'latin' => null], $payload);
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertNull($decoded->second);
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
            ['first' => 'Argüelles', 'second' => null, 'given' => 'Ana', 'latin' => 'ARGUELLES<<ANA'],
            $payload,
        );
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('ARGUELLES<<ANA', $decoded->latinOverride()?->value);
    }

    public function testDecodeAcceptsMissingSecondAndLatin(): void
    {
        $name = new PersonNameCodec()->decode(['first' => 'García', 'given' => 'Ana']);

        self::assertNull($name->second);
        self::assertNull($name->latinOverride());
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'first is an int' => [['first' => 1, 'given' => 'Ana']];
        yield 'first missing' => [['given' => 'Ana']];
        yield 'given missing' => [['first' => 'García']];
        yield 'given is an int' => [['first' => 'García', 'given' => 1]];
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
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage(self::SHAPE);

        new PersonNameCodec()->decode($payload);
    }

    public function testTheJsonViewIsNotACodecPayload(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage(self::SHAPE);

        new PersonNameCodec()->decode(
            new PersonName(FamilyName::from('García'), null, GivenName::from('Ana'))->jsonSerialize(),
        );
    }

    public function testDecodeRefusesAnInvalidSecondSurname(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid Spanish family name (invalid character).');

        new PersonNameCodec()->decode(['first' => 'García', 'second' => 'Лопес', 'given' => 'Ana']);
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            PersonNameCodec::class . ' encodes ' . PersonName::class . ', ' . GivenName::class . ' given.',
        );

        new PersonNameCodec()->encode(GivenName::from('Ana'));
    }

    public function testWorksThroughTheMapperFromUntrustedInput(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        $name = $mapper->fromUntrustedPayload(
            PersonName::class,
            ['first' => 'Muñoz', 'second' => null, 'given' => 'Ana', 'latin' => 'munxxoz<<ana'],
        );

        self::assertSame('MUNXXOZ<<ANA', $name->latin()->value);
        $expected = ['first' => 'Muñoz', 'second' => null, 'given' => 'Ana', 'latin' => 'MUNXXOZ<<ANA'];
        self::assertSame($expected, $mapper->toPayload($name));
    }
}
