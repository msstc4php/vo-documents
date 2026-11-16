<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\CA;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\CA\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\CA\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\CA\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CA.md "Names".
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
#[CoversClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(AnyIcaoSpelling::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Text::class)]
#[UsesClass(Optional::class)]
#[UsesClass(LatinForm::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'French accents dropped, hyphens kept' => [
            'Bérubé-Lévesque',
            'Jean-François',
            'BERUBE-LEVESQUE<<JEAN-FRANCOIS',
        ];
        yield 'apostrophe omitted' => ["D'Amours", 'Hélène', 'DAMOURS<<HELENE'];
        yield 'particles, two given names' => ['de la Fontaine', 'Marie Ève', 'DE<LA<FONTAINE<<MARIE<EVE'];
        yield 'diaeresis' => ['Noël', 'Zoë', 'NOEL<<ZOE'];
        yield 'German umlaut' => ['Müller', 'Jürgen', 'MUELLER<<JUERGEN'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testAPersonWithoutAFamilyNameHasASurnameOnly(): void
    {
        $name = new PersonName(FamilyName::from('Kiawenti'), null);

        self::assertSame('KIAWENTI', $name->latin()->value);
        self::assertSame('Kiawenti', $name->full());
        self::assertNull($name->given);
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(FamilyName::from("Gagne\u{0301}"), GivenName::from('Marie Ève'));

        self::assertSame('Gagné', $name->family->value);
        self::assertSame('Marie Ève Gagné', $name->full());
    }

    public function testALetterWithoutAnIcaoSpellingIsRefusedWithoutAnOverride(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(FamilyName::from('Kĸ'), GivenName::from('Marie')),
            'Kĸ Marie',
            'Invalid personal name (no ICAO transliteration for a character).',
        );
    }

    public function testALetterWithoutAnIcaoSpellingIsAcceptedWithAnOverride(): void
    {
        $name = new PersonName(FamilyName::from('Kĸ'), GivenName::from('Marie'), IcaoPersonName::from('KK<<MARIE'));

        self::assertSame('KK<<MARIE', $name->latin()->value);
        self::assertSame('KK<<MARIE', $name->latinOverride()?->value);
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'given names dropped' => ['Tremblay', 'Marie Ève Louise', 'TREMBLAY<<MARIE', 'TREMBLAY<<MARIE'];
        yield 'given names inverted' => ['Tremblay', 'Marie Ève', 'TREMBLAY<<EVE<MARIE', 'TREMBLAY<<EVE<MARIE'];
        yield 'alternative spelling' => ['Müller', 'Jürgen', 'MILLER<<JURGEN', 'MILLER<<JURGEN'];
        yield 'filler for the hyphen, lower case' => [
            'Bérubé-Lévesque',
            'Anne',
            'berube<levesque<<annie',
            'BERUBE<LEVESQUE<<ANNIE',
        ];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('acceptedOverrides')]
    public function testKeepsAnyIcaoSpellingAsTheOverride(
        string $family,
        string $given,
        string $latin,
        string $stored,
    ): void {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given), IcaoPersonName::from($latin));

        self::assertSame($stored, $name->latin()->value);
        self::assertSame($stored, $name->latinOverride()?->value);
    }

    public function testAnOverrideThatIsTheDerivedFormIsNotKept(): void
    {
        $name = new PersonName(
            FamilyName::from('Bérubé-Lévesque'),
            GivenName::from('Anne'),
            IcaoPersonName::from('berube-levesque<<anne'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Bérubé-Lévesque'), GivenName::from('Anne'))));
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Gagné'), GivenName::from('Anne'));
        $override = new PersonName(
            FamilyName::from('Gagné'),
            GivenName::from('Anne'),
            IcaoPersonName::from('GAGNEE<<ANNE'),
        );

        self::assertTrue($derived->equals(new PersonName(FamilyName::from('Gagné'), GivenName::from('Anne'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertTrue($override->equals(new PersonName(
            FamilyName::from('Gagné'),
            GivenName::from('Anne'),
            IcaoPersonName::from('gagnee<<anne'),
        )));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Gagne'), GivenName::from('Anne'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Gagné'), GivenName::from('Anna'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Gagné'), null)));
        self::assertFalse(new PersonName(FamilyName::from('Gagné'), null)->equals($derived));
        self::assertTrue(
            new PersonName(FamilyName::from('Gagné'), null)->equals(new PersonName(FamilyName::from('Gagné'), null)),
        );
        self::assertFalse($derived->equals(GivenName::from('Anne')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => "D'Amours", 'given' => 'Hélène', 'effectiveLatin' => 'DAMOURS<<HELENE'],
            new PersonName(FamilyName::from("D'Amours"), GivenName::from('Hélène'))->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Kiawenti', 'given' => null, 'effectiveLatin' => 'KIAWENTI'],
            new PersonName(FamilyName::from('Kiawenti'), null)->jsonSerialize(),
        );
    }
}
