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
use MSSTC4PHP\ValueObject\Documents\Name\ES;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\MX;
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
     * A name serialized by 0.3 or 0.4: the codec payload with the override under its 0.x key `latin`.
     */
    private const string CODEC_PAYLOAD_04_DE = 'O:50:"MSSTC4PHP\ValueObject\Documents\Name\DE\PersonName":3:{'
        . 's:6:"family";s:7:"Müller";s:5:"given";s:7:"Jürgen";s:5:"latin";s:14:"MULLER<<JURGEN";}';

    /**
     * Names serialized by 0.2.0 and 0.4.0 with the surnames under their 0.x names `first` and `second`, captured
     * from those releases.
     */
    private const string LEGACY_ES = 'O:50:"MSSTC4PHP\\ValueObject\\Documents\\Name\\ES\\PersonName":5:{s:77:"' . "\0" . AbstractLatinPersonName::class . "\0" . 'effective";O:47:"MSSTC4PHP\\ValueObject\\Documents\\Icao\\PersonName":1:{s:5:"value";s:24:"GARCIA<LOPEZ<<MARIA<JOSE";}s:76:"' . "\0" . AbstractLatinPersonName::class . "\0" . 'override";N;s:5:"first";O:50:"MSSTC4PHP\\ValueObject\\Documents\\Name\\ES\\FamilyName":1:{s:5:"value";s:7:"García";}s:6:"second";O:50:"MSSTC4PHP\\ValueObject\\Documents\\Name\\ES\\FamilyName":1:{s:5:"value";s:6:"López";}s:5:"given";O:49:"MSSTC4PHP\\ValueObject\\Documents\\Name\\ES\\GivenName":1:{s:5:"value";s:12:"María José";}}';

    private const string LEGACY_MX = 'O:50:"MSSTC4PHP\\ValueObject\\Documents\\Name\\MX\\PersonName":5:{s:77:"' . "\0" . AbstractLatinPersonName::class . "\0" . 'effective";O:47:"MSSTC4PHP\\ValueObject\\Documents\\Icao\\PersonName":1:{s:5:"value";s:15:"HERNANDEZ<<JOSE";}s:76:"' . "\0" . AbstractLatinPersonName::class . "\0" . 'override";N;s:5:"first";O:50:"MSSTC4PHP\\ValueObject\\Documents\\Name\\MX\\FamilyName":1:{s:5:"value";s:10:"Hernández";}s:6:"second";N;s:5:"given";O:49:"MSSTC4PHP\\ValueObject\\Documents\\Name\\MX\\GivenName":1:{s:5:"value";s:5:"José";}}';

    private const string CODEC_PAYLOAD_04_ES = 'O:50:"MSSTC4PHP\\ValueObject\\Documents\\Name\\ES\\PersonName":4:{s:5:"first";s:7:"García";s:6:"second";s:6:"López";s:5:"given";s:12:"María José";s:5:"latin";N;}';

    private const string CODEC_PAYLOAD_04_MX = 'O:50:"MSSTC4PHP\\ValueObject\\Documents\\Name\\MX\\PersonName":4:{s:5:"first";s:10:"Hernández";s:6:"second";N;s:5:"given";s:5:"José";s:5:"latin";N;}';

    /**
     * The property keys of PHP's default layout that the 0.2 read path depends on, frozen until 2.0: public
     * properties by their 0.2 name, private ones as "DeclaringClass::name" (relative to the package namespace).
     */
    private const string PACKAGE = 'MSSTC4PHP\\ValueObject\\Documents\\';

    private const array RENAMED_SINCE_02 = ['firstSurname' => 'first', 'secondSurname' => 'second'];

    private const array KEYS_04 = ['latinOverride' => 'latin', 'firstSurname' => 'first', 'secondSurname' => 'second'];

    private const array LAYOUT_02 = [
        'Name\\RU\\PersonName' => ['Name\\RU\\PersonName::effective', 'Name\\RU\\PersonName::latin', 'family', 'given', 'patronymic'],
        'Name\\AU\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\DE\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\FR\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\IT\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\ES\\PersonName' => ['first', 'second', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\NL\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\CH\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\GB\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\TR\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\US\\PersonName' => ['family', 'given', 'middle', 'suffix', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\CA\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\MX\\PersonName' => ['first', 'second', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\BR\\PersonName' => ['family', 'given', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\CN\\PersonName' => ['family', 'given', 'Name\\RequiredLatin\\AbstractRequiredLatinPersonName::latin'],
        'Name\\JP\\PersonName' => ['Name\\JP\\PersonName::effective', 'Name\\JP\\PersonName::override', 'family', 'given', 'familyReading', 'givenReading'],
        'Name\\KR\\PersonName' => ['family', 'given', 'Name\\RequiredLatin\\AbstractRequiredLatinPersonName::latin'],
        'Name\\IN\\PersonName' => ['given', 'surname', 'Name\\Latin\\AbstractLatinPersonName::effective', 'Name\\Latin\\AbstractLatinPersonName::override'],
        'Name\\IN\\Devanagari\\PersonName' => ['given', 'surname', 'Name\\IN\\Devanagari\\PersonName::latin'],
        'Name\\ID\\PersonName' => ['name', 'Name\\ID\\PersonName::latin'],
        'Name\\SA\\PersonName' => ['given', 'father', 'grandfather', 'family', 'Name\\SA\\PersonName::latin'],
    ];

    /**
     * @return iterable<string, array{string, array<string, string|null>}>
     */
    public static function payloads(): iterable
    {
        $samples = [
            'RU' => ['family' => 'Иванов', 'given' => 'Иван', 'patronymic' => 'Иванович', 'latinOverride' => null],
            'AU' => ['family' => 'Citizen', 'given' => 'Zoë', 'latinOverride' => null],
            'DE' => ['family' => 'Schmidt', 'given' => 'Jürgen', 'latinOverride' => null],
            'FR' => ['family' => 'Dupont', 'given' => 'Joël', 'latinOverride' => null],
            'IT' => ['family' => 'Rossi', 'given' => 'Nicolò', 'latinOverride' => null],
            'ES' => ['firstSurname' => 'García', 'secondSurname' => 'López', 'given' => 'María José', 'latinOverride' => null],
            'NL' => ['family' => 'van der Berg', 'given' => 'Jan Pieter', 'latinOverride' => null],
            'CH' => ['family' => 'Schweizer Sample', 'given' => 'Helvetia', 'latinOverride' => null],
            'GB' => ['family' => "O'Brien", 'given' => 'Siân', 'latinOverride' => null],
            'TR' => ['family' => 'Yılmaz', 'given' => 'İnci', 'latinOverride' => null],
            'US' => ['family' => "O'Brien", 'given' => 'Mary Ann', 'middle' => 'Lee', 'suffix' => '3RD', 'latinOverride' => null],
            'CA' => ['family' => "D'Amours", 'given' => 'Hélène', 'latinOverride' => null],
            'MX' => ['firstSurname' => 'Hernández', 'secondSurname' => 'de la Cruz', 'given' => 'José', 'latinOverride' => null],
            'BR' => ['family' => 'da Silva', 'given' => 'Maria da Conceição', 'latinOverride' => null],
            'CN' => ['family' => "阿沛\u{00B7}阿旺晋美", 'given' => null, 'latin' => 'NGAPOI<<NGAWANG<JIGME'],
            'JP' => ['family' => '大野', 'given' => '翔', 'familyReading' => 'オオノ', 'givenReading' => 'しょう', 'latinOverride' => null],
            'KR' => ['family' => '마이클 존슨', 'given' => null, 'latin' => 'JOHNSON<<MICHAEL'],
            'IN' => ['given' => 'Jürgen', 'surname' => 'Müller', 'latinOverride' => 'MULLER<<JURGEN'],
            'IN Devanagari' => ['given' => 'पीयूष कुमार', 'surname' => 'जैन', 'latin' => 'JAIN<<PIYUSH<KUMAR'],
            'ID' => ['name' => 'Siti Nurhaliza', 'latin' => 'NURHALIZA<<SITI'],
            'SA' => ['given' => 'محمد', 'father' => 'سلمان', 'grandfather' => 'سعد', 'family' => 'آل سعود', 'latin' => 'AL<SAUD<<MOHAMMED'],
            'RU with a kept Latin form' => ['family' => 'Иванова', 'given' => 'Юлия', 'patronymic' => null, 'latinOverride' => 'IVANOVA<<YULIYA'],
            'JP with a kept Latin form' => ['family' => '大野', 'given' => '翔', 'familyReading' => 'オオノ', 'givenReading' => 'しょう', 'latinOverride' => 'OHNO<<SHO'],
            'DE with a kept Latin form' => ['family' => 'Müller', 'given' => 'Jürgen', 'latinOverride' => 'MULLER<<JURGEN'],
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

    /**
     * @param array<string, string|null> $payload
     */
    #[DataProvider('payloads')]
    public function testANameReadsBackFromItsJsonView(string $class, array $payload): void
    {
        [$codec, $name] = $this->decoded($class, $payload);

        $view = $name->jsonSerialize();

        self::assertIsArray($view);
        self::assertTrue($name->equals($codec->decode($view)));
    }

    /**
     * The names that derive their Latin form: their 0.x payloads stored the override under `latin`.
     *
     * @return iterable<string, array{string, array<string, string|null>}>
     */
    public static function derivedPayloads(): iterable
    {
        foreach (self::payloads() as $label => [$class, $payload]) {
            if (\array_key_exists('latinOverride', $payload)) {
                yield $label => [$class, $payload];
            }
        }
    }

    /**
     * @param array<string, string|null> $payload
     */
    #[DataProvider('derivedPayloads')]
    public function testA0xCodecPayloadStillDecodes(string $class, array $payload): void
    {
        [$codec, $name] = $this->decoded($class, $payload);
        $legacy = [];
        foreach ($payload as $key => $value) {
            $legacy[self::KEYS_04[$key] ?? $key] = $value;
        }

        self::assertTrue($name->equals($codec->decode($legacy)));
    }

    public function testAName0304SerializedStillLoads(): void
    {
        $restored = $this->restored(self::CODEC_PAYLOAD_04_DE);

        self::assertInstanceOf(PersonName::class, $restored);
        self::assertSame('MULLER<<JURGEN', $restored->latinOverride()?->value);
        self::assertTrue(
            new PersonName(FamilyName::from('Müller'), GivenName::from('Jürgen'), IcaoPersonName::from('MULLER<<JURGEN'))
                ->equals($restored),
        );
    }

    /**
     * @return iterable<string, array{string, ES\PersonName|MX\PersonName}>
     */
    public static function renamedSurnames(): iterable
    {
        $es = new ES\PersonName(
            ES\FamilyName::from('García'),
            ES\FamilyName::from('López'),
            ES\GivenName::from('María José'),
        );
        $mx = new MX\PersonName(MX\FamilyName::from('Hernández'), null, MX\GivenName::from('José'));

        yield 'ES, 0.2' => [self::LEGACY_ES, $es];
        yield 'MX, 0.2' => [self::LEGACY_MX, $mx];
        yield 'ES, 0.4' => [self::CODEC_PAYLOAD_04_ES, $es];
        yield 'MX, 0.4' => [self::CODEC_PAYLOAD_04_MX, $mx];
    }

    #[DataProvider('renamedSurnames')]
    public function testANameWithTheSurnamesUnderTheir0xNamesStillLoads(string $serialized, NationalPersonName $expected): void
    {
        self::assertTrue($expected->equals($this->restored($serialized)));
    }

    public function testThe020LayoutIsPinnedUntil20(): void
    {
        $layouts = [];
        foreach (Codecs::all() as $codec) {
            $layouts[\substr($codec->valueObjectClass(), \strlen(self::PACKAGE))] = $this->layoutKeys($codec->valueObjectClass());
        }

        self::assertSame(self::LAYOUT_02, $layouts);
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
     * @param class-string $class
     *
     * @return list<string>
     */
    private function layoutKeys(string $class): array
    {
        $keys = [];
        for ($reflection = new ReflectionClass($class); $reflection !== false; $reflection = $reflection->getParentClass()) {
            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic() || $property->getDeclaringClass()->name !== $reflection->name) {
                    continue;
                }

                $keys[] = $property->isPrivate()
                    ? \substr($reflection->name, \strlen(self::PACKAGE)) . '::' . $property->name
                    : self::RENAMED_SINCE_02[$property->name] ?? $property->name;
            }
        }

        return $keys;
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

                $key = $property->isPrivate()
                    ? "\0" . $class->name . "\0" . $property->name
                    : self::RENAMED_SINCE_02[$property->name] ?? $property->name;
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
