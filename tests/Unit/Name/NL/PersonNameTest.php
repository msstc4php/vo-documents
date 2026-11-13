<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\NL;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\NL\AbstractDutchNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\NL\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\NL\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\NL\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/NL.md "Names"; `de Bruijn, Willeke Liselotte` is the RvIG passport specimen.
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
#[CoversClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractDutchNamePart::class)]
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
#[UsesClass(LatinForm::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string NOT_A_TRANSLITERATION =
        'Invalid Dutch person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'RvIG specimen' => ['de Bruijn', 'Willeke Liselotte', 'DE<BRUIJN<<WILLEKE<LISELOTTE'];
        yield 'two prefixes' => ['van der Berg', 'Jan Pieter', 'VAN<DER<BERG<<JAN<PIETER'];
        yield 'apostrophe prefix omitted' => ["'t Hart", 'Anna', 'T<HART<<ANNA'];
        yield 'apostrophe inside omitted' => ["d'Ancona", 'Hedda', 'DANCONA<<HEDDA'];
        yield 'diaeresis' => ['Hoëvell', 'Noël', 'HOEVELL<<NOEL'];
        yield 'hyphenated given name' => ['Jansen-de Vries', 'Hendrik-Jan', 'JANSEN-DE<VRIES<<HENDRIK-JAN'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testOneNameIsAFamilyNameOnly(): void
    {
        $name = new PersonName(FamilyName::from('Soekarno'), null);

        self::assertSame('SOEKARNO', $name->latin()->value);
        self::assertSame('Soekarno', $name->full());
        self::assertNull($name->given);
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(FamilyName::from("van der Hoe\u{0308}vell"), GivenName::from('Jan Pieter'));

        self::assertSame('van der Hoëvell', $name->family->value);
        self::assertSame('Jan Pieter van der Hoëvell', $name->full());
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
        yield 'u umlaut without e' => ['Müller', 'Anna', 'MULLER<<ANNA', 'MULLER<<ANNA'];
        yield 'filler for the hyphen, lower case' => [
            'Jansen-Müller',
            'Anna',
            'jansen<muller<<anna',
            'JANSEN-MULLER<<ANNA',
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
            FamilyName::from('van der Berg'),
            GivenName::from('Jan'),
            IcaoPersonName::from('van<der<berg<<jan'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('van der Berg'), GivenName::from('Jan'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'prefix dropped' => ['BRUIJN<<WILLEKE'];
        yield 'ij as Y' => ['DE<BRUYN<<WILLEKE'];
        yield 'parts swapped' => ['WILLEKE<<DE<BRUIJN'];
        yield 'one name for two' => ['DE<BRUIJN'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from('de Bruijn'),
                GivenName::from('Willeke'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['de Bruijn', 'Willeke', $latin]),
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

    public function testPrefixCaseIsPartOfTheValue(): void
    {
        $lower = new PersonName(FamilyName::from('van der Berg'), GivenName::from('Jan'));
        $upper = new PersonName(FamilyName::from('Van der Berg'), GivenName::from('Jan'));

        self::assertFalse($lower->equals($upper));
        self::assertSame($lower->latin()->value, $upper->latin()->value);
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'de Bruijn', 'given' => 'Willeke', 'effectiveLatin' => 'DE<BRUIJN<<WILLEKE'],
            new PersonName(FamilyName::from('de Bruijn'), GivenName::from('Willeke'))->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Soekarno', 'given' => null, 'effectiveLatin' => 'SOEKARNO'],
            new PersonName(FamilyName::from('Soekarno'), null)->jsonSerialize(),
        );
    }
}
