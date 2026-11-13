<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\RU;

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
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonNameCodec as AuPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonNameCodec as DePersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonNameCodec as FrPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonNameCodec as ItPersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\RU\AbstractRussianNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\RU\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\Patronymic;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\RU\RussianTransliteration;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonNameCodec::class)]
#[UsesClass(Codecs::class)]
#[UsesClass(AuPersonNameCodec::class)]
#[UsesClass(DePersonNameCodec::class)]
#[UsesClass(FrPersonNameCodec::class)]
#[UsesClass(ItPersonNameCodec::class)]
#[UsesClass(PersonName::class)]
#[UsesClass(RussianTransliteration::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractRussianNamePart::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(AnyIcaoSpelling::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(Patronymic::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(PayloadFields::class)]
final class PersonNameCodecTest extends TestCase
{
    private const string SHAPE = 'Expected a Russian person name payload with string "family" and "given", '
        . 'string or null "patronymic" and "latin", and no other keys.';

    public function testMapsItsClass(): void
    {
        self::assertSame(PersonName::class, new PersonNameCodec()->valueObjectClass());
    }

    public function testRoundTripWithoutOverride(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(FamilyName::from('Иванов'), GivenName::from('Иван'), Patronymic::from('Иванович'));

        $payload = $codec->encode($name);

        self::assertSame(
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 'Иванович', 'latin' => null],
            $payload,
        );
        self::assertTrue($codec->decode($payload)->equals($name));
    }

    public function testRoundTripWithOverrideAndNoPatronymic(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            FamilyName::from('Иванова'),
            GivenName::from('Юлия'),
            null,
            IcaoPersonName::from('IVANOVA<<YULIYA'),
        );

        $payload = $codec->encode($name);

        self::assertSame(
            ['family' => 'Иванова', 'given' => 'Юлия', 'patronymic' => null, 'latin' => 'IVANOVA<<YULIYA'],
            $payload,
        );
        $decoded = $codec->decode($payload);
        self::assertTrue($decoded->equals($name));
        self::assertSame('IVANOVA<<YULIYA', $decoded->latinOverride()?->value);
    }

    public function testAnOverrideThatIsTheDerivedFormIsStoredAsNull(): void
    {
        $name = new PersonName(
            FamilyName::from('Иванов'),
            GivenName::from('Иван'),
            null,
            IcaoPersonName::from('IVANOV<<IVAN'),
        );

        self::assertNull(new PersonNameCodec()->encode($name)['latin']);
    }

    public function testDecodeAcceptsMissingOptionalKeys(): void
    {
        $name = new PersonNameCodec()->decode(['family' => 'Иванов', 'given' => 'Иван']);

        self::assertNull($name->patronymic);
        self::assertNull($name->latinOverride());
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'family is an int' => [['family' => 1]];
        yield 'given missing' => [['family' => 'Иванов']];
        yield 'family missing' => [['given' => 'Иван']];
        yield 'given is an int' => [['family' => 'Иванов', 'given' => 1]];
        yield 'patronymic is an int' => [['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 1]];
        yield 'latin is an int' => [['family' => 'Иванов', 'given' => 'Иван', 'latin' => 1]];
        yield 'unknown key' => [
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => null, 'latin' => null, 'extra' => 'x'],
        ];
        yield 'int key' => [['family' => 'Иванов', 'given' => 'Иван', 0 => 'x']];
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
        $name = new PersonName(
            FamilyName::from('Иванов'),
            GivenName::from('Иван'),
            null,
            IcaoPersonName::from('IVANOV<<IVAN'),
        );

        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage(self::SHAPE);

        new PersonNameCodec()->decode($name->jsonSerialize());
    }

    public function testDecodeRefusesAnInvalidPart(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonNameCodec()->decode(['family' => 'Ivanov', 'given' => 'Иван', 'patronymic' => null, 'latin' => null]);
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            PersonNameCodec::class . ' encodes ' . PersonName::class . ', ' . GivenName::class . ' given.',
        );

        new PersonNameCodec()->encode(GivenName::from('Иван'));
    }

    public function testWorksThroughTheMapperFromUntrustedInput(): void
    {
        $mapper = new ValueObjectMapper(Codecs::all());

        $name = $mapper->fromUntrustedPayload(
            PersonName::class,
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 'Иванович', 'latin' => 'ivanov<<ivan<ivanovich'],
        );

        self::assertSame('IVANOV<<IVAN<IVANOVICH', $name->latin()->value);
        self::assertSame(
            ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 'Иванович', 'latin' => 'IVANOV<<IVAN<IVANOVICH'],
            $mapper->toPayload($name),
        );
    }
}
