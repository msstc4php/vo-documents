<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\ES;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\ES\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/ES.md "Names".
 */
#[CoversClass(PersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
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
    private const string NOT_A_TRANSLITERATION =
        'Invalid Spanish person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string|null, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'two surnames, compound given name' => ['García', 'López', 'María José', 'GARCIA<LOPEZ<<MARIA<JOSE'];
        yield 'one surname' => ['Smith', null, 'John', 'SMITH<<JOHN'];
        yield 'eñe takes the default N' => ['Muñoz', 'Ibáñez', 'Íñigo', 'MUNOZ<IBANEZ<<INIGO'];
        yield 'diaeresis takes the default UE' => ['Argüelles', null, 'Ana', 'ARGUEELLES<<ANA'];
        yield 'particles, upper in Latin' => ['de la Fuente', 'del Río', 'Pilar', 'DE<LA<FUENTE<DEL<RIO<<PILAR'];
        yield 'apostrophe omitted, hyphen kept' => ["d'Ors", 'Rovira', 'Josep-Lluís', 'DORS<ROVIRA<<JOSEP-LLUIS'];
        yield 'l with middle dot' => ["Ge\u{0140}la", null, 'Marta', 'GELLA<<MARTA'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $first, ?string $second, string $given, string $expected): void
    {
        $name = new PersonName(
            FamilyName::from($first),
            $second === null ? null : FamilyName::from($second),
            GivenName::from($given),
        );

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(FamilyName::from('García'), FamilyName::from('López'), GivenName::from("Mari\u{0301}a"));

        self::assertSame('María', $name->given->value);
        self::assertSame('María García López', $name->full());
        self::assertSame(
            'John Smith',
            new PersonName(FamilyName::from('Smith'), null, GivenName::from('John'))->full(),
        );
    }

    /**
     * @return iterable<string, array{string, string|null, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'diaeresis as plain U' => ['Argüelles', null, 'ARGUELLES<<ANA', 'ARGUELLES<<ANA'];
        yield 'eñe as NXX, lower case' => ['Muñoz', 'García', 'munxxoz<garcia<<ana', 'MUNXXOZ<GARCIA<<ANA'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('acceptedOverrides')]
    public function testAcceptsAnotherIcaoSpelling(string $first, ?string $second, string $latin, string $stored): void
    {
        $name = new PersonName(
            FamilyName::from($first),
            $second === null ? null : FamilyName::from($second),
            GivenName::from('Ana'),
            IcaoPersonName::from($latin),
        );

        self::assertSame($stored, $name->latin()->value);
        self::assertSame($stored, $name->latinOverride()?->value);
    }

    public function testAnOverrideThatIsTheDerivedFormIsNotKept(): void
    {
        $name = new PersonName(
            FamilyName::from('Muñoz'),
            null,
            GivenName::from('Ana'),
            IcaoPersonName::from('munoz<<ana'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Muñoz'), null, GivenName::from('Ana'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['PEREZ<LOPEZ<<ANA'];
        yield 'second surname dropped' => ['GARCIA<<ANA'];
        yield 'surnames as two identifiers' => ['GARCIA<<LOPEZ<ANA'];
        yield 'surnames swapped' => ['LOPEZ<GARCIA<<ANA'];
        yield 'eñe spelt NY' => ['GARCIA<LOPEZ<<ANYA'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage(self::NOT_A_TRANSLITERATION);

        new PersonName(
            FamilyName::from('García'),
            FamilyName::from('López'),
            GivenName::from('Aña'),
            IcaoPersonName::from($latin),
        );
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Argüelles'), FamilyName::from('Pérez'), GivenName::from('Ana'));
        $override = new PersonName(
            FamilyName::from('Argüelles'),
            FamilyName::from('Pérez'),
            GivenName::from('Ana'),
            IcaoPersonName::from('ARGUELLES<PEREZ<<ANA'),
        );

        self::assertTrue($derived->equals(
            new PersonName(FamilyName::from('Argüelles'), FamilyName::from('Pérez'), GivenName::from('Ana')),
        ));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertTrue($override->equals(new PersonName(
            FamilyName::from('Argüelles'),
            FamilyName::from('Pérez'),
            GivenName::from('Ana'),
            IcaoPersonName::from('arguelles<perez<<ana'),
        )));
        self::assertFalse($derived->equals(
            new PersonName(FamilyName::from('Arguelles'), FamilyName::from('Pérez'), GivenName::from('Ana')),
        ));
        self::assertFalse($derived->equals(
            new PersonName(FamilyName::from('Argüelles'), FamilyName::from('Perez'), GivenName::from('Ana')),
        ));
        self::assertFalse($derived->equals(
            new PersonName(FamilyName::from('Argüelles'), FamilyName::from('Pérez'), GivenName::from('Anna')),
        ));
        self::assertFalse($derived->equals(GivenName::from('Ana')));
    }

    public function testEqualityTellsOneSurnameFromTwo(): void
    {
        $one = new PersonName(FamilyName::from('García'), null, GivenName::from('Ana'));
        $two = new PersonName(FamilyName::from('García'), FamilyName::from('López'), GivenName::from('Ana'));

        self::assertTrue($one->equals(new PersonName(FamilyName::from('García'), null, GivenName::from('Ana'))));
        self::assertFalse($one->equals($two));
        self::assertFalse($two->equals($one));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['first' => 'García', 'second' => null, 'given' => 'Ana', 'effectiveLatin' => 'GARCIA<<ANA'],
            new PersonName(FamilyName::from('García'), null, GivenName::from('Ana'))->jsonSerialize(),
        );
        self::assertSame(
            ['first' => 'García', 'second' => 'López', 'given' => 'Ana', 'effectiveLatin' => 'GARCIA<LOPEZ<<ANA'],
            new PersonName(FamilyName::from('García'), FamilyName::from('López'), GivenName::from('Ana'))
                ->jsonSerialize(),
        );
    }
}
