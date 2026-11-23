<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\IN;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\AbstractDevanagariNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\GivenName as DevanagariGivenName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\IndianFullName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\PersonNameCodec;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Surname;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "Names": given name first, surname optional (MEA: "leave the Surname column
 * blank and write your full name in Applicant's Given Name").
 */
#[CoversClass(PersonName::class)]
#[CoversClass(PersonNameCodec::class)]
#[UsesClass(AbstractLatinPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(Surname::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(IdentifierSpellings::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(TransliterationVariant::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Optional::class)]
#[UsesClass(IndianFullName::class)]
#[UsesClass(PayloadFields::class)]
#[UsesClass(Text::class)]
#[UsesClass(LatinForm::class)]
#[UsesClass(AbstractDevanagariNamePart::class)]
#[UsesClass(DevanagariGivenName::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string SHAPE = 'Expected an Indian person name payload with string "given", '
        . 'string, null or missing "surname" and "latinOverride", and no other keys.';

    private const string NOT_A_TRANSLITERATION =
        'Invalid Indian person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string|null, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'MEA example: surname JAIN, given PIYUSH KUMAR' => ['Piyush Kumar', 'Jain', 'JAIN<<PIYUSH<KUMAR'];
        yield 'surname of two words' => ['Aditi', 'Roy Choudhary', 'ROY<CHOUDHARY<<ADITI'];
        yield 'hyphenated surname' => ['Aditi', 'Das-Gupta', 'DAS-GUPTA<<ADITI'];
        yield 'apostrophe omitted' => ['Maria', "D'Souza", 'DSOUZA<<MARIA'];
        yield 'mononym, one word' => ['Rajesh', null, 'RAJESH'];
        yield 'mononym, two words' => ['Rajesh Kumar', null, 'RAJESH<KUMAR'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $given, ?string $surname, string $expected): void
    {
        $name = new PersonName(GivenName::from($given), $surname === null ? null : Surname::from($surname));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testFullNameIsGivenThenSurname(): void
    {
        self::assertSame(
            'Piyush Kumar Jain',
            new PersonName(GivenName::from('Piyush Kumar'), Surname::from('Jain'))->full(),
        );
    }

    public function testMononymIsTheGivenNameAlone(): void
    {
        $name = new PersonName(GivenName::from('Rajesh Kumar'));

        self::assertSame('Rajesh Kumar', $name->full());
        self::assertNull($name->surname);
        self::assertSame(
            ['given' => 'Rajesh Kumar', 'surname' => null, 'latin' => 'RAJESH<KUMAR'],
            $name->jsonSerialize(),
        );
    }

    /**
     * @return iterable<string, array{string, string|null, non-empty-string, non-empty-string|null}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'diaeresis as plain U' => ['Jürgen', 'Müller', 'MULLER<<JURGEN', 'MULLER<<JURGEN'];
        yield 'lower case is upper-cased' => ['Jürgen', 'Müller', 'muller<<jurgen', 'MULLER<<JURGEN'];
        yield 'mononym spelling' => ['Jürgen', null, 'JURGEN', 'JURGEN'];
        yield 'the derived spelling is no override' => ['Rajesh', 'Jain', 'JAIN<<RAJESH', null];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('acceptedOverrides')]
    public function testAcceptsAnotherIcaoSpelling(
        string $given,
        ?string $surname,
        string $latin,
        ?string $stored,
    ): void {
        $name = new PersonName(
            GivenName::from($given),
            $surname === null ? null : Surname::from($surname),
            IcaoPersonName::from($latin),
        );

        self::assertSame($stored ?? $latin, $name->latin()->value);
        self::assertSame($stored, $name->latinOverride()?->value);
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['KHAN<<RAJESH'];
        yield 'given name dropped' => ['JAIN'];
        yield 'names swapped' => ['RAJESH<<JAIN'];
        yield 'surname as a secondary identifier' => ['KUMAR<<JAIN<RAJESH'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(
                GivenName::from('Rajesh'),
                Surname::from('Jain'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['Rajesh', 'Jain', $latin]),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(GivenName::from('Jürgen'), Surname::from('Müller'));
        $override = new PersonName(
            GivenName::from('Jürgen'),
            Surname::from('Müller'),
            IcaoPersonName::from('MULLER<<JURGEN'),
        );

        self::assertTrue($derived->equals(new PersonName(GivenName::from('Jürgen'), Surname::from('Müller'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertFalse($derived->equals(new PersonName(GivenName::from('Jurgen'), Surname::from('Müller'))));
        self::assertFalse($derived->equals(new PersonName(GivenName::from('Jürgen'), Surname::from('Muller'))));
        self::assertFalse($derived->equals(new PersonName(GivenName::from('Jürgen'))));
        self::assertFalse(new PersonName(GivenName::from('Jürgen'))->equals($derived));
        self::assertFalse($derived->equals(GivenName::from('Jürgen')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['given' => 'Piyush Kumar', 'surname' => 'Jain', 'latin' => 'JAIN<<PIYUSH<KUMAR'],
            new PersonName(GivenName::from('Piyush Kumar'), Surname::from('Jain'))->jsonSerialize(),
        );
    }

    public function testTwoLongestPartsFitTheLatinForm(): void
    {
        $part = \str_repeat('a', 100);

        self::assertSame(202, \strlen(new PersonName(GivenName::from($part), Surname::from($part))->latin()->value));
    }

    public function testDevanagariPartsAreRefusedWithoutEchoingThem(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(GivenName::from('राजेश')),
            'राजेश',
            'Invalid Indian given name (invalid character).',
        );
    }

    public function testCodecRoundTrip(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(
            GivenName::from('Jürgen'),
            Surname::from('Müller'),
            IcaoPersonName::from('MULLER<<JURGEN'),
        );

        $payload = $codec->encode($name);

        self::assertSame(['given' => 'Jürgen', 'surname' => 'Müller', 'latinOverride' => 'MULLER<<JURGEN'], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
        self::assertSame(PersonName::class, $codec->valueObjectClass());
    }

    public function testCodecRoundTripOfAMononymStoresNoDerivedLatinForm(): void
    {
        $codec = new PersonNameCodec();
        $name = new PersonName(GivenName::from('Rajesh Kumar'));

        $payload = $codec->encode($name);

        self::assertSame(['given' => 'Rajesh Kumar', 'surname' => null, 'latinOverride' => null], $payload);
        self::assertTrue($codec->decode($payload)->equals($name));
        self::assertTrue($codec->decode(['given' => 'Rajesh Kumar'])->equals($name));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function malformedPayloads(): iterable
    {
        yield 'given missing' => [['surname' => 'Jain']];
        yield 'given null' => [['given' => null, 'surname' => 'Jain']];
        yield 'given int' => [['given' => 1]];
        yield 'surname int' => [['given' => 'Rajesh', 'surname' => 1]];
        yield 'latin int' => [['given' => 'Rajesh', 'latinOverride' => 1]];
        yield 'unknown key' => [['given' => 'Rajesh', 'x' => 'y']];
        yield 'family key of other countries' => [['family' => 'Jain', 'given' => 'Rajesh']];
        yield 'both the 0.x and the 1.0 override key' => [
            ['given' => 'Rajesh', 'surname' => 'Jain', 'latin' => null, 'latinOverride' => null],
        ];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function testDecodeRefusesAMalformedPayload(array $payload): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode($payload),
            self::payloadProbe($payload),
            self::SHAPE,
            InvalidPayloadException::class,
        );
    }

    public function testDecodeRefusesInvalidParts(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonNameCodec()->decode(['given' => 'Rajesh', 'surname' => 'Раджеш']),
            'Rajesh Раджеш',
            'Invalid Indian surname (invalid character).',
        );
    }

    public function testEncodeRefusesAnotherValueObject(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): array => new PersonNameCodec()->encode(DevanagariGivenName::from('राजेश')),
            'राजेश',
            PersonNameCodec::class . ' encodes ' . PersonName::class . ', ' . DevanagariGivenName::class . ' given.',
            LogicException::class,
        );
    }
}
