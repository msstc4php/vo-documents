<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\NameProperties;
use MSSTC4PHP\ValueObject\Documents\Internal\ReparsesThroughCodec;
use MSSTC4PHP\ValueObject\Documents\Name\DE\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\DE\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversTrait(ReparsesThroughCodec::class)]
#[CoversClass(NameProperties::class)]
#[UsesNamespace('MSSTC4PHP\ValueObject\Documents')]
final class SerializationTest extends TestCase
{
    /**
     * A name serialized by 0.2.0 (PHP's default layout), captured from that release.
     */
    private const string LEGACY_DE = 'O:50:"MSSTC4PHP\ValueObject\Documents\Name\DE\PersonName":4:{s:77:"' . "\0"
        . AbstractLatinPersonName::class . "\0"
        . 'effective";O:47:"MSSTC4PHP\ValueObject\Documents\Icao\PersonName":1:{s:5:"value";s:16:"MUELLER<<JUERGEN";}'
        . 's:76:"' . "\0" . AbstractLatinPersonName::class . "\0" . 'override";N;'
        . 's:6:"family";O:50:"MSSTC4PHP\ValueObject\Documents\Name\DE\FamilyName":1:{s:5:"value";s:7:"Müller";}'
        . 's:5:"given";O:49:"MSSTC4PHP\ValueObject\Documents\Name\DE\GivenName":1:{s:5:"value";s:7:"Jürgen";}}';

    /**
     * @return iterable<string, array{string, array<string, string|null>}>
     */
    public static function payloads(): iterable
    {
        $samples = [
            'RU' => ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 'Иванович', 'latin' => null],
            'AU' => ['family' => 'Citizen', 'given' => 'Zoë', 'latin' => null],
            'DE' => ['family' => 'Schmidt', 'given' => 'Jürgen', 'latin' => null],
            'FR' => ['family' => 'Dupont', 'given' => 'Joël', 'latin' => null],
            'IT' => ['family' => 'Rossi', 'given' => 'Nicolò', 'latin' => null],
            'ES' => ['first' => 'García', 'second' => 'López', 'given' => 'María José', 'latin' => null],
            'NL' => ['family' => 'van der Berg', 'given' => 'Jan Pieter', 'latin' => null],
            'CH' => ['family' => 'Schweizer Sample', 'given' => 'Helvetia', 'latin' => null],
            'GB' => ['family' => "O'Brien", 'given' => 'Siân', 'latin' => null],
            'TR' => ['family' => 'Yılmaz', 'given' => 'İnci', 'latin' => null],
            'US' => ['family' => "O'Brien", 'given' => 'Mary Ann', 'middle' => 'Lee', 'suffix' => '3RD', 'latin' => null],
            'CA' => ['family' => "D'Amours", 'given' => 'Hélène', 'latin' => null],
            'MX' => ['first' => 'Hernández', 'second' => 'de la Cruz', 'given' => 'José', 'latin' => null],
            'BR' => ['family' => 'da Silva', 'given' => 'Maria da Conceição', 'latin' => null],
            'CN' => ['family' => "阿沛\u{00B7}阿旺晋美", 'given' => null, 'latin' => 'NGAPOI<<NGAWANG<JIGME'],
            'JP' => ['family' => '大野', 'given' => '翔', 'familyReading' => 'オオノ', 'givenReading' => 'しょう', 'latin' => null],
            'KR' => ['family' => '마이클 존슨', 'given' => null, 'latin' => 'JOHNSON<<MICHAEL'],
            'IN' => ['given' => 'Jürgen', 'surname' => 'Müller', 'latin' => 'MULLER<<JURGEN'],
            'IN Devanagari' => ['given' => 'पीयूष कुमार', 'surname' => 'जैन', 'latin' => 'JAIN<<PIYUSH<KUMAR'],
            'ID' => ['name' => 'Siti Nurhaliza', 'latin' => 'NURHALIZA<<SITI'],
            'SA' => ['given' => 'محمد', 'father' => 'سلمان', 'grandfather' => 'سعد', 'family' => 'آل سعود', 'latin' => 'AL<SAUD<<MOHAMMED'],
            'RU with a kept Latin form' => ['family' => 'Иванова', 'given' => 'Юлия', 'patronymic' => null, 'latin' => 'IVANOVA<<YULIYA'],
            'JP with a kept Latin form' => ['family' => '大野', 'given' => '翔', 'familyReading' => 'オオノ', 'givenReading' => 'しょう', 'latin' => 'OHNO<<SHO'],
            'DE with a kept Latin form' => ['family' => 'Müller', 'given' => 'Jürgen', 'latin' => 'MULLER<<JURGEN'],
        ];

        foreach ($samples as $label => $payload) {
            $country = \str_replace(' ', '\\', \explode(' with ', $label)[0]);

            yield $label => ['MSSTC4PHP\\ValueObject\\Documents\\Name\\' . $country . '\\PersonName', $payload];
        }
    }

    public function testEveryCodecHasASample(): void
    {
        $classes = [];
        foreach (self::payloads() as [$class, $payload]) {
            $classes[] = $this->decoded($class, $payload)[1]::class;
        }

        self::assertEqualsCanonicalizing(
            \array_map(static fn (PayloadCodec $codec): string => $codec->valueObjectClass(), Codecs::all()),
            \array_values(\array_unique($classes)),
        );
    }

    /**
     * @param array<string, string|null> $payload
     */
    #[DataProvider('payloads')]
    public function testANameSerializesAsItsCodecPayload(string $class, array $payload): void
    {
        [$codec, $name] = $this->decoded($class, $payload);
        $serialized = \serialize($name);

        self::assertSame(\sprintf('O:%d:"%s":%d:{%s}', \strlen($class), $class, \count($payload), $this->body($codec->encode($name))), $serialized);
        self::assertTrue($name->equals($this->restored($serialized)));
    }

    /**
     * @param array<string, string|null> $payload
     */
    #[DataProvider('payloads')]
    public function testANameInTheDefaultLayoutOf020StillLoads(string $class, array $payload): void
    {
        [, $name] = $this->decoded($class, $payload);

        self::assertTrue($name->equals($this->restored($this->defaultLayout($name))));
    }

    public function testThe020PayloadCapturedFromTheReleaseLoads(): void
    {
        $expected = new PersonName(FamilyName::from('Müller'), GivenName::from('Jürgen'));
        $restored = $this->restored(self::LEGACY_DE);

        self::assertTrue($expected->equals($restored));
        self::assertSame(\serialize($expected), \serialize($restored));
    }

    public function testADerivedLatinFormIsRebuiltNotRestored(): void
    {
        $forged = \str_replace('s:16:"MUELLER<<JUERGEN"', 's:16:"MALLORY<<EVEEVEE"', self::LEGACY_DE);

        $restored = $this->restored($forged);

        self::assertInstanceOf(PersonName::class, $restored);
        self::assertSame('MUELLER<<JUERGEN', $restored->latin()->value);
    }

    public function testAForgedLatinOverrideIsRefused(): void
    {
        $forged = \str_replace(
            "\0override\";N;",
            "\0override\";" . \serialize(IcaoPersonName::from('MALLORY<<EVE')),
            self::LEGACY_DE,
        );

        $this->expectException(InvalidPersonNameException::class);

        \unserialize($forged);
    }

    public function testAForgedPayloadIsRefused(): void
    {
        $serialized = \serialize(new PersonName(FamilyName::from('Schmidt'), GivenName::from('Jürgen')));

        $this->expectException(InvalidPersonNameException::class);

        \unserialize(\str_replace('N;}', 's:12:"MALLORY<<EVE";}', $serialized));
    }

    public function testAPayloadOfTheWrongShapeIsRefused(): void
    {
        $this->expectException(InvalidPayloadException::class);

        \unserialize(\sprintf('O:%d:"%s":1:{s:6:"family";s:7:"Schmidt";}', \strlen(PersonName::class), PersonName::class));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function malformedLegacyPayloads(): iterable
    {
        yield 'missing given' => [\preg_replace('/s:5:"given";O:49:.*\}\}$/', '}', \str_replace(':4:{', ':3:{', self::LEGACY_DE)) ?? ''];
        yield 'family as a string' => [\preg_replace('/s:6:"family";O:50:"[^"]+":1:\{s:5:"value";s:7:"Müller";\}/', 's:6:"family";s:7:"Müller";', self::LEGACY_DE) ?? ''];
    }

    #[DataProvider('malformedLegacyPayloads')]
    public function testAMalformedLegacyPayloadIsRefusedWithoutEchoingIt(string $payload): void
    {
        $this->expectExceptionObject(new InvalidValueObjectException('A serialized ' . PersonName::class . ' is malformed.'));

        \unserialize($payload);
    }

    public function testExtraKeysOfALegacyPayloadAreIgnored(): void
    {
        $payload = \str_replace(':4:{', ':5:{s:5:"extra";i:1;', self::LEGACY_DE);

        self::assertTrue(new PersonName(FamilyName::from('Müller'), GivenName::from('Jürgen'))->equals($this->restored($payload)));
    }

    public function testThePropertiesOfAClassAreReflectedOnce(): void
    {
        $first = NameProperties::of(PersonName::class);

        self::assertNotSame([], $first);
        self::assertSame($first[0], NameProperties::of(PersonName::class)[0]);
    }

    public function testUnserializeRefusesAnInstanceThatHoldsAValue(): void
    {
        $name = new PersonName(FamilyName::from('Schmidt'), GivenName::from('Jürgen'));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A value object is immutable; __unserialize() runs only on a new object.');

        $name->__unserialize(['family' => 'Dupont', 'given' => 'Joël', 'latin' => null]);
    }

    /**
     * @param array<string, string|null> $payload
     *
     * @return array{PayloadCodec<ValueObject>, NationalPersonName}
     */
    private function decoded(string $class, array $payload): array
    {
        foreach (Codecs::all() as $codec) {
            if ($codec->valueObjectClass() === $class) {
                $name = $codec->decode($payload);
                self::assertInstanceOf(NationalPersonName::class, $name);
                self::assertSame($payload, $codec->encode($name));

                return [$codec, $name];
            }
        }

        self::fail(\sprintf('No codec for %s.', $class));
    }

    /**
     * @param array<string, scalar|null> $payload
     */
    private function body(array $payload): string
    {
        $body = '';
        foreach ($payload as $key => $value) {
            $body .= \serialize($key) . \serialize($value);
        }

        return $body;
    }

    /**
     * PHP's default serialization of the object, as 0.2 wrote it before the class defined __serialize().
     */
    private function defaultLayout(object $object): string
    {
        $body = '';
        $count = 0;
        for ($class = new ReflectionClass($object); $class !== false; $class = $class->getParentClass()) {
            foreach ($class->getProperties() as $property) {
                if ($property->isStatic() || $property->getDeclaringClass()->name !== $class->name) {
                    continue;
                }

                $key = $property->isPrivate() ? "\0" . $class->name . "\0" . $property->name : $property->name;
                $body .= \serialize($key) . \serialize($property->getValue($object));
                $count++;
            }
        }

        return \sprintf('O:%d:"%s":%d:{%s}', \strlen($object::class), $object::class, $count, $body);
    }

    private function restored(string $serialized): ValueObject
    {
        $object = \unserialize($serialized);
        self::assertInstanceOf(ValueObject::class, $object);

        return $object;
    }
}
