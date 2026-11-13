<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\CH;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CH\AbstractSwissNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CH\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\CH\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\CH\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CH.md "Names"; `Schweizer Sample, Helvetia` is the fedpol identity card specimen.
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
#[CoversClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractSwissNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(IdentifierSpellings::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(TransliterationVariant::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Text::class)]
#[UsesClass(Optional::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string NOT_A_TRANSLITERATION =
        'Invalid Swiss person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'fedpol specimen' => ['Schweizer Sample', 'Helvetia', 'SCHWEIZER<SAMPLE<<HELVETIA'];
        yield 'German umlauts' => ['Müller-Brunner', 'Jürg', 'MUELLER-BRUNNER<<JUERG'];
        yield 'French' => ['Lefèvre', 'François', 'LEFEVRE<<FRANCOIS'];
        yield 'Italian apostrophe omitted' => ["D'Amico", 'Niccolò', 'DAMICO<<NICCOLO'];
        yield 'Romansh' => ['Caflisch', 'Gian Duri', 'CAFLISCH<<GIAN<DURI'];
        yield 'particle' => ['de Weck', 'Anne-Sophie', 'DE<WECK<<ANNE-SOPHIE'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testAPersonWithoutAGivenNameHasAFamilyNameOnly(): void
    {
        $name = new PersonName(FamilyName::from('Müller'), null);

        self::assertSame('MUELLER', $name->latin()->value);
        self::assertSame('Müller', $name->full());
        self::assertNull($name->given);
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(FamilyName::from("Mu\u{0308}ller"), GivenName::from('Anna Maria'));

        self::assertSame('Müller', $name->family->value);
        self::assertSame('Anna Maria Müller', $name->full());
    }

    public function testALetterWithoutAnIcaoSpellingIsRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonName(FamilyName::from('Kĸ'), GivenName::from('Anna'));
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'u umlaut without e' => ['Müller', 'Jürg', 'MULLER<<JURG', 'MULLER<<JURG'];
        yield 'filler for the hyphen, lower case' => [
            'Müller-Brunner',
            'Anna',
            'muller<brunner<<anna',
            'MULLER-BRUNNER<<ANNA',
        ];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('acceptedOverrides')]
    public function testAcceptsAnotherIcaoSpelling(string $family, string $given, string $latin, string $stored): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given), IcaoPersonName::from($latin));

        self::assertSame($stored, $name->latin()->value);
        self::assertSame($stored, $name->latinOverride()?->value);
    }

    public function testAnOverrideThatIsTheDerivedFormIsNotKept(): void
    {
        $name = new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Anna'),
            IcaoPersonName::from('mueller<<anna'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Anna'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['MEIER<<ANNA'];
        yield 'umlaut as UU' => ['MUULLER<<ANNA'];
        yield 'parts swapped' => ['ANNA<<MUELLER'];
        yield 'given name dropped' => ['MUELLER'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from('Müller'),
                GivenName::from('Anna'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['Müller', 'Anna', $latin]),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Müller'), GivenName::from('Anna'));
        $override = new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Anna'),
            IcaoPersonName::from('MULLER<<ANNA'),
        );

        self::assertTrue($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Anna'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertTrue($override->equals(new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Anna'),
            IcaoPersonName::from('muller<<anna'),
        )));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Mueller'), GivenName::from('Anna'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Anne'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Müller'), null)));
        self::assertFalse(new PersonName(FamilyName::from('Müller'), null)->equals($derived));
        self::assertTrue(
            new PersonName(FamilyName::from('Müller'), null)->equals(new PersonName(FamilyName::from('Müller'), null)),
        );
        self::assertFalse($derived->equals(GivenName::from('Anna')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'Schweizer Sample', 'given' => 'Helvetia', 'effectiveLatin' => 'SCHWEIZER<SAMPLE<<HELVETIA'],
            new PersonName(FamilyName::from('Schweizer Sample'), GivenName::from('Helvetia'))->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Müller', 'given' => null, 'effectiveLatin' => 'MUELLER'],
            new PersonName(FamilyName::from('Müller'), null)->jsonSerialize(),
        );
    }
}
