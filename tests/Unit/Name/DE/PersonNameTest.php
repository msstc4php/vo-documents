<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\DE;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\DE\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\DE\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\DE\PersonName;
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
 * Expectations follow docs/sources/DE.md "Names".
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
        'Invalid German person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'umlauts take the default spelling' => ['Müller', 'Jürgen', 'MUELLER<<JUERGEN'];
        yield 'o umlaut' => ['Schröder', 'Gerhard', 'SCHROEDER<<GERHARD'];
        yield 'sharp s' => ['Groß', 'Lieselotte', 'GROSS<<LIESELOTTE'];
        yield 'capital sharp s' => ['GROẞ', 'Ann', 'GROSS<<ANN'];
        yield 'particles kept in lower case, upper in Latin' => ['von der Leyen', 'Ursula', 'VON<DER<LEYEN<<URSULA'];
        yield 'double name and several given names' => [
            'Müller-Lüdenscheidt',
            'Hans Peter',
            'MUELLER-LUEDENSCHEIDT<<HANS<PETER',
        ];
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
        $name = new PersonName(FamilyName::from("Mu\u{0308}ller"), GivenName::from('Anna Maria'));

        self::assertSame('Müller', $name->family->value);
        self::assertSame('Anna Maria Müller', $name->full());
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'umlauts without e' => ['Müller', 'Jürgen', 'MULLER<<JURGEN', 'MULLER<<JURGEN'];
        yield 'UXX spelling of Ü' => ['Müller', 'Jürgen', 'MUXXLLER<<JUERGEN', 'MUXXLLER<<JUERGEN'];
        yield 'filler for the hyphen, lower case' => [
            'Müller-Lüdenscheidt',
            'Ann',
            'muller<ludenscheidt<<ann',
            'MULLER-LUDENSCHEIDT<<ANN',
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
        $name = new PersonName(FamilyName::from('Groß'), GivenName::from('Ann'), IcaoPersonName::from('gross<<ann'));

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Groß'), GivenName::from('Ann'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['SCHMIDT<<JURGEN'];
        yield 'English spelling, not in the table' => ['MILLER<<JURGEN'];
        yield 'sharp s as B' => ['MUELLER<<JUERGEN<B'];
        yield 'parts swapped' => ['JURGEN<<MULLER'];
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
                GivenName::from('Jürgen'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['Müller', 'Jürgen', $latin]),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testSharpSHasOnlyOneSpelling(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from('Groß'),
                GivenName::from('Ann'),
                IcaoPersonName::from('GROS<<ANN'),
            ),
            \implode(' ', ['Groß', 'Ann', 'GROS<<ANN']),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Müller'), GivenName::from('Jürgen'));
        $override = new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Jürgen'),
            IcaoPersonName::from('MULLER<<JURGEN'),
        );

        self::assertTrue($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Jürgen'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertTrue($override->equals(new PersonName(
            FamilyName::from('Müller'),
            GivenName::from('Jürgen'),
            IcaoPersonName::from('muller<<jurgen'),
        )));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Mueller'), GivenName::from('Jürgen'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Müller'), GivenName::from('Juergen'))));
        self::assertFalse($derived->equals(GivenName::from('Jürgen')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'Müller', 'given' => 'Jürgen', 'effectiveLatin' => 'MUELLER<<JUERGEN'],
            new PersonName(FamilyName::from('Müller'), GivenName::from('Jürgen'))->jsonSerialize(),
        );
    }

    public function testTheVariantCapIsPerWordSoManyUmlautsAcceptAnOverride(): void
    {
        $given = \implode(' ', \array_fill(0, 13, 'Jürgen'));
        $name = new PersonName(
            FamilyName::from('Müller Lüdenscheid'),
            GivenName::from($given),
            IcaoPersonName::from('MULLER<LUDENSCHEID<<' . \str_repeat('JURGEN<', 12) . 'JURGEN'),
        );

        self::assertSame(
            'MULLER<LUDENSCHEID<<' . \str_repeat('JURGEN<', 12) . 'JURGEN',
            $name->latinOverride()?->value,
        );
    }
}
