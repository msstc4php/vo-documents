<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\FR;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\FR\AbstractFrenchNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\FR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\FR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\FR\PersonName;
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
 * Expectations follow docs/sources/FR.md "Names".
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
#[CoversClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractFrenchNamePart::class)]
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
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string NOT_A_TRANSLITERATION =
        'Invalid French person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'accents dropped' => ['Lefèvre', 'Hélène', 'LEFEVRE<<HELENE'];
        yield 'cedilla' => ['Garçon', 'François', 'GARCON<<FRANCOIS'];
        yield 'ligatures' => ['Cœur', 'Lætitia', 'COEUR<<LAETITIA'];
        yield 'diaeresis takes the default spelling' => ['Müller', 'Joël', 'MUELLER<<JOEL'];
        yield 'y with diaeresis' => ['Aÿ', 'Zoé', 'AY<<ZOE'];
        yield 'apostrophe omitted' => ["D'Artagnan", 'Charles', 'DARTAGNAN<<CHARLES'];
        yield 'particles kept in lower case, upper in Latin' => [
            'de La Fontaine',
            'Jean-Pierre',
            'DE<LA<FONTAINE<<JEAN-PIERRE',
        ];
        yield 'several given names' => ['Martin-Dupont', 'Marie Ève', 'MARTIN-DUPONT<<MARIE<EVE'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(FamilyName::from("Lefe\u{0300}vre"), GivenName::from('Marie Hélène'));

        self::assertSame('Lefèvre', $name->family->value);
        self::assertSame('Marie Hélène Lefèvre', $name->full());
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'diaeresis without e' => ['Müller', 'Joël', 'MULLER<<JOEL', 'MULLER<<JOEL'];
        yield 'filler for the hyphen, lower case' => [
            'Müller-Lefèvre',
            'Ann',
            'muller<lefevre<<ann',
            'MULLER-LEFEVRE<<ANN',
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
        $name = new PersonName(FamilyName::from('Cœur'), GivenName::from('Zoé'), IcaoPersonName::from('coeur<<zoe'));

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Cœur'), GivenName::from('Zoé'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['DUPONT<<JOEL'];
        yield 'English spelling, not in the table' => ['MILLER<<JOEL'];
        yield 'ligature split differently' => ['MULLER<<JOL'];
        yield 'parts swapped' => ['JOEL<<MULLER'];
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
                GivenName::from('Joël'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['Müller', 'Joël', $latin]),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testTheLigatureHasOnlyOneSpelling(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from('Cœur'),
                GivenName::from('Ann'),
                IcaoPersonName::from('COUR<<ANN'),
            ),
            \implode(' ', ['Cœur', 'Ann', 'COUR<<ANN']),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Müller'), GivenName::from('Joël'));
        $override = new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Joël'),
            IcaoPersonName::from('MULLER<<JOEL'),
        );

        self::assertTrue($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Joël'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertTrue($override->equals(new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Joël'),
            IcaoPersonName::from('muller<<joel'),
        )));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Mueller'), GivenName::from('Joël'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Joel'))));
        self::assertFalse($derived->equals(GivenName::from('Joël')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'Cœur', 'given' => 'Zoé', 'effectiveLatin' => 'COEUR<<ZOE'],
            new PersonName(FamilyName::from('Cœur'), GivenName::from('Zoé'))->jsonSerialize(),
        );
    }
}
