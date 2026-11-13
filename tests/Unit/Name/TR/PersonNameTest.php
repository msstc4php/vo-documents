<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\TR;

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
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TR\AbstractTurkishNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\TR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\TR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\TR\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/TR.md "Names".
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
#[CoversClass(AbstractFamilyGivenPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractTurkishNamePart::class)]
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
        'Invalid Turkish person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'capital dotted and dotless I' => ['IŞIK', 'İNCİ', 'ISIK<<INCI'];
        yield 'small dotless and dotted i' => ['ılgaz', 'irmak', 'ILGAZ<<IRMAK'];
        yield 'mixed case' => ['Işık', 'İnci', 'ISIK<<INCI'];
        yield 'cedilla and breve' => ['Çağlayan', 'Ayşe', 'CAGLAYAN<<AYSE'];
        yield 'o and u umlaut take the default spelling' => ['Öztürk', 'Gül', 'OEZTUERK<<GUEL'];
        yield 'circumflex' => ['Kâhya', 'Âdem Nûr', 'KAHYA<<ADEM<NUR'];
        yield 'two surnames and two given names' => ['Yılmaz Kaya', 'Ahmet Can', 'YILMAZ<KAYA<<AHMET<CAN'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    /**
     * The derivation must not depend on the process locale: under a Turkish LC_CTYPE a locale-aware upper-casing would
     * turn `i` into `İ` and `ı` into `I` in other ways.
     */
    public function testTheDerivationDoesNotDependOnTheLocale(): void
    {
        $previous = \setlocale(\LC_ALL, '0');
        if (\setlocale(\LC_ALL, 'tr_TR.UTF-8', 'tr_TR.utf8', 'tr_TR', 'tr') === false) {
            self::markTestSkipped('No Turkish locale is installed.');
        }

        try {
            $name = new PersonName(FamilyName::from('ılgaz'), GivenName::from('irmak İnci'));
        } finally {
            \setlocale(\LC_ALL, $previous === false ? 'C' : $previous);
        }

        self::assertSame('ILGAZ<<IRMAK<INCI', $name->latin()->value);
        self::assertSame('irmak İnci ılgaz', $name->full());
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(FamilyName::from("O\u{0308}ztu\u{0308}rk"), GivenName::from('Ayşe  Nur'));

        self::assertSame('Öztürk', $name->family->value);
        self::assertSame('Ayşe Nur Öztürk', $name->full());
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'PRADO specimen MRZ' => ['Örnek', 'Zeynep', 'ORNEK<<ZEYNEP', 'ORNEK<<ZEYNEP'];
        yield 'umlauts without e, lower case' => ['Öztürk', 'Gül', 'ozturk<<gul', 'OZTURK<<GUL'];
        yield 'UXX spelling of Ü' => ['Öztürk', 'Gül', 'OZTUXXRK<<GUL', 'OZTUXXRK<<GUL'];
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
        $name = new PersonName(FamilyName::from('Işık'), GivenName::from('İnci'), IcaoPersonName::from('isik<<inci'));

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Işık'), GivenName::from('İnci'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['KAYA<<INCI'];
        yield 'dotted I written as a letter pair' => ['ISIK<<IENCI'];
        yield 'S cedilla as SH' => ['ISHIK<<INCI'];
        yield 'parts swapped' => ['INCI<<ISIK'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from('Işık'),
                GivenName::from('İnci'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['Işık', 'İnci', $latin]),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Öztürk'), GivenName::from('Gül'));
        $override = new PersonName(
            FamilyName::from('Öztürk'),
            GivenName::from('Gül'),
            IcaoPersonName::from('OZTURK<<GUL'),
        );

        self::assertTrue($derived->equals(new PersonName(FamilyName::from('Öztürk'), GivenName::from('Gül'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertTrue($override->equals(
            new PersonName(FamilyName::from('Öztürk'), GivenName::from('Gül'), IcaoPersonName::from('ozturk<<gul')),
        ));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Ozturk'), GivenName::from('Gül'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Öztürk'), GivenName::from('Gul'))));
        self::assertFalse($derived->equals(GivenName::from('Gül')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'Işık', 'given' => 'İnci', 'effectiveLatin' => 'ISIK<<INCI'],
            new PersonName(FamilyName::from('Işık'), GivenName::from('İnci'))->jsonSerialize(),
        );
    }

    public function testTheVariantCapIsPerWordSoManyUmlautsAcceptAnOverride(): void
    {
        $given = \implode(' ', \array_fill(0, 9, 'Ömür'));
        $name = new PersonName(
            FamilyName::from('Öztürk'),
            GivenName::from($given),
            IcaoPersonName::from('OZTURK<<' . \str_repeat('OMUR<', 8) . 'OMUR'),
        );

        self::assertSame('OZTURK<<' . \str_repeat('OMUR<', 8) . 'OMUR', $name->latinOverride()?->value);
    }
}
