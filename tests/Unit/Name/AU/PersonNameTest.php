<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\AU;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AU\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\AU\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\AU\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/AU.md "Names".
 */
#[CoversClass(PersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(LatinSpelling::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(TransliterationVariant::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(UnicodeText::class)]
final class PersonNameTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'diaeresis' => ['Citizen', 'Zoë', 'CITIZEN<<ZOE'];
        yield 'umlaut takes the default spelling' => ['Müller', 'Jürgen', 'MUELLER<<JUERGEN'];
        yield 'apostrophe omitted' => ["O'Brien", "D'Arcy", 'OBRIEN<<DARCY'];
        yield 'several given names' => ['Citizen', 'Joan Catherine', 'CITIZEN<<JOAN<CATHERINE'];
        yield 'hyphen and particles kept' => ['van der Berg-Smith', 'Mary-Anne', 'VAN<DER<BERG-SMITH<<MARY-ANNE'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testFullNamePutsGivenNamesFirst(): void
    {
        self::assertSame(
            'Joan Catherine Citizen',
            new PersonName(FamilyName::from('Citizen'), GivenName::from('Joan Catherine'))->full(),
        );
    }

    public function testOverrideWithAnotherIcaoSpellingIsAccepted(): void
    {
        $name = new PersonName(FamilyName::from('Müller'), GivenName::from('Zoë'), IcaoPersonName::from('muller<<zoe'));

        self::assertSame('MULLER<<ZOE', $name->latin()->value);
        self::assertSame('MULLER<<ZOE', $name->latinOverride()?->value);
    }

    public function testOverrideMayWriteAHyphenAsAFiller(): void
    {
        $name = new PersonName(FamilyName::from('Müller-Smith'), GivenName::from('Ann'), IcaoPersonName::from('MULLER<SMITH<<ANN'));

        self::assertSame('MULLER-SMITH<<ANN', $name->latin()->value);
        self::assertSame('MULLER-SMITH<<ANN', $name->latinOverride()?->value);
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedOverrides(): iterable
    {
        yield 'same spelling in another case' => ['Müller', 'Zoë', 'mueller<<zoe'];
        yield 'filler for the hyphen' => ['Smith-Jones', 'Ann', 'SMITH<JONES<<ANN'];
        yield 'MRZ padding' => ['Citizen', 'Joan', 'CITIZEN<<JOAN<<<<<<'];
    }

    #[DataProvider('derivedOverrides')]
    public function testAnOverrideThatIsTheDerivedFormIsNotKept(string $family, string $given, string $latin): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given), IcaoPersonName::from($latin));

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from($family), GivenName::from($given))));
    }

    public function testANameWithManySpellingsIsAcceptedWithoutAnOverride(): void
    {
        $name = new PersonName(FamilyName::from(\str_repeat('Ä', 13)), GivenName::from('Ann'));

        self::assertSame(\str_repeat('AE', 13) . '<<ANN', $name->latin()->value);
    }

    public function testTheDerivedFormOfANameWithTooManySpellingsIsAcceptedAsAnOverride(): void
    {
        $derived = \str_repeat('AE', 13) . '<<ANN';
        $name = new PersonName(FamilyName::from(\str_repeat('Ä', 13)), GivenName::from('Ann'), IcaoPersonName::from($derived));

        self::assertSame($derived, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testPartsAreMatchedOneByOneSoTheirProductIsNotBounded(): void
    {
        // 3^7 spellings of the family name times 2 of the given name: above 4096 together, each part below it.
        $name = new PersonName(FamilyName::from('Üüüüüüü'), GivenName::from('Ä'), IcaoPersonName::from('UXXUUEUUUU<<A'));

        self::assertSame('UXXUUEUUUU<<A', $name->latinOverride()?->value);
    }

    public function testALigatureListedByTable6AIsAcceptedAndTakesItsTableSpelling(): void
    {
        $name = new PersonName(FamilyName::from("\u{0132}sselmeer"), GivenName::from('Ann'));

        self::assertSame("\u{0132}sselmeer", $name->family->value);
        self::assertSame('IJSSELMEER<<ANN', $name->latin()->value);
    }

    public function testAFullwidthNameIsRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid Australian family name (compatibility character).');

        new PersonName(FamilyName::from("\u{FF33}\u{FF4D}\u{FF49}\u{FF54}\u{FF48}"), GivenName::from('Ann'));
    }

    public function testALatinLetterOutsideTheTableIsSpelledAsItsBaseLetter(): void
    {
        $name = new PersonName(FamilyName::from('Nguyễn'), GivenName::from('Ștefan Țurcanu'));

        self::assertSame('NGUYEN<<STEFAN<TURCANU', $name->latin()->value);
        self::assertNull(new PersonName(FamilyName::from('Nguyễn'), GivenName::from('Ana'), IcaoPersonName::from('NGUYEN<<ANA'))->latinOverride());
    }

    public function testAnOverrideOfANameWithTooManySpellingsIsRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid personal name (too many transliteration variants).');

        new PersonName(FamilyName::from(\str_repeat('Ä', 13)), GivenName::from('Ann'), IcaoPersonName::from('A<<ANN'));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['SMITH<<JOHN'];
        yield 'given name missing' => ['MULLER'];
        yield 'spelling outside the table' => ['MILLER<<ZOE'];
        yield 'parts swapped' => ['ZOE<<MULLER'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid Australian person name (the Latin form is not a transliteration of the name).');

        new PersonName(FamilyName::from('Müller'), GivenName::from('Zoë'), IcaoPersonName::from($latin));
    }

    public function testRefusesALatinLetterWithoutAnIcaoSpellingWithoutEchoingIt(): void
    {
        try {
            new PersonName(FamilyName::from('Citizen'), GivenName::from('ƒred'));
        } catch (InvalidPersonNameException $exception) {
            self::assertSame('Invalid personal name (no ICAO transliteration for a character).', $exception->getMessage());
            self::assertStringNotContainsString('ƒ', $exception->getMessage());
            self::assertStringNotContainsString('red', $exception->getMessage());

            return;
        }

        self::fail('Expected an InvalidPersonNameException.');
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Müller'), GivenName::from('Zoë'));
        $override = new PersonName(FamilyName::from('Müller'), GivenName::from('Zoë'), IcaoPersonName::from('MULLER<<ZOE'));

        self::assertTrue($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Zoë'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertTrue($override->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Zoë'), IcaoPersonName::from('muller<<zoe'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Muller'), GivenName::from('Zoë'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Zoe'))));
        self::assertFalse($derived->equals(GivenName::from('Zoë')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'Müller', 'given' => 'Zoë', 'effectiveLatin' => 'MUELLER<<ZOE'],
            new PersonName(FamilyName::from('Müller'), GivenName::from('Zoë'))->jsonSerialize(),
        );
    }
}
