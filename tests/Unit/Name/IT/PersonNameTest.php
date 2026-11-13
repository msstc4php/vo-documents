<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\IT;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\IT\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\IT\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\IT\PersonName;
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
 * Expectations follow docs/sources/IT.md "Names".
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
#[CoversClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
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
#[UsesClass(Optional::class)]
#[UsesClass(Text::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string NOT_A_TRANSLITERATION =
        'Invalid Italian person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'grave accent' => ['Rossi', 'Nicolò', 'ROSSI<<NICOLO'];
        yield 'elided particle, apostrophe omitted' => ["D'Angelo", 'Maria', 'DANGELO<<MARIA'];
        yield 'particle and two given names' => ['De Luca', 'Maria Grazia', 'DE<LUCA<<MARIA<GRAZIA'];
        yield 'lower-case particle, upper in Latin' => ['di Pietro', 'Antonio', 'DI<PIETRO<<ANTONIO'];
        yield 'double surname, hyphenated given name' => ['Rossi-Bianchi', 'Gian-Luca', 'ROSSI-BIANCHI<<GIAN-LUCA'];
        yield 'umlaut takes the default spelling' => ['Müller', 'Anna', 'MUELLER<<ANNA'];
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
        $name = new PersonName(FamilyName::from('De Luca'), GivenName::from("Nicolo\u{0300} Maria"));

        self::assertSame('Nicolò Maria', $name->given->value);
        self::assertSame('Nicolò Maria De Luca', $name->full());
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'umlaut without e' => ['Müller', 'Anna', 'MULLER<<ANNA', 'MULLER<<ANNA'];
        yield 'filler for the hyphen, lower case' => [
            'Müller-Rossi',
            'Anna',
            'muller<rossi<<anna',
            'MULLER-ROSSI<<ANNA',
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
            FamilyName::from("D'Angelo"),
            GivenName::from('Nicolò'),
            IcaoPersonName::from('dangelo<<nicolo'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from("D'Angelo"), GivenName::from('Nicolò'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['BIANCHI<<NICOLO'];
        yield 'apostrophe as a filler' => ['D<ANGELO<<NICOLO'];
        yield 'accent spelt as a letter' => ['DANGELO<<NICOLOO'];
        yield 'parts swapped' => ['NICOLO<<DANGELO'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from("D'Angelo"),
                GivenName::from('Nicolò'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['Angelo"), GivenName::from(', "D'Angelo", $latin]),
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
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Ana'))));
        self::assertFalse($derived->equals(GivenName::from('Anna')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'De Luca', 'given' => 'Nicolò', 'effectiveLatin' => 'DE<LUCA<<NICOLO'],
            new PersonName(FamilyName::from('De Luca'), GivenName::from('Nicolò'))->jsonSerialize(),
        );
    }
}
