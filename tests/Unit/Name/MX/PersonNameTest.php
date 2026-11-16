<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\MX;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\LatinSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\MX\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\MX\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\MX\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/MX.md "Names".
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
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
#[UsesClass(LatinForm::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string NOT_A_TRANSLITERATION =
        'Invalid Mexican person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string|null, string|null, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'two surnames, two given names' => ['Hernández', 'López', 'José Ángel', 'HERNANDEZ<LOPEZ<<JOSE<ANGEL'];
        yield 'one surname' => ['Garduño', null, 'Julio Tomás', 'GARDUNO<<JULIO<TOMAS'];
        yield 'lower-case particles, upper in Latin' => ['Ramírez', 'de la Cruz', 'Ana', 'RAMIREZ<DE<LA<CRUZ<<ANA'];
        yield 'compound surname' => ['Riva Palacio', 'Cruz', 'Rocío', 'RIVA<PALACIO<CRUZ<<ROCIO'];
        yield 'diaeresis takes the default UE' => ['Argüello', 'Pérez', 'Dalia', 'ARGUEELLO<PEREZ<<DALIA'];
        yield 'apostrophe omitted, hyphen kept' => ["O'Hara", 'López-Castillo', 'Juan', 'OHARA<LOPEZ-CASTILLO<<JUAN'];
        yield 'no surnames: the given names alone' => [null, null, 'Juan', 'JUAN'];
        yield 'no surnames, two given names' => [null, null, 'María José', 'MARIA<JOSE'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(?string $first, ?string $second, string $given, string $expected): void
    {
        $name = new PersonName(
            $first === null ? null : FamilyName::from($first),
            $second === null ? null : FamilyName::from($second),
            GivenName::from($given),
        );

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testFullNameOrder(): void
    {
        self::assertSame(
            'José Ángel Hernández de la Cruz',
            new PersonName(FamilyName::from('Hernández'), FamilyName::from('de la Cruz'), GivenName::from('José Ángel'))
                ->full(),
        );
        self::assertSame(
            'Margarita Zaro',
            new PersonName(FamilyName::from('Zaro'), null, GivenName::from('Margarita'))->full(),
        );
        self::assertSame('Nancy', new PersonName(null, null, GivenName::from('Nancy'))->full());
    }

    public function testRefusesASecondSurnameWithoutAFirst(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(null, FamilyName::from('Zárate'), GivenName::from('Federico')),
            'Zárate Federico',
            'Invalid Mexican person name (second surname without a first surname).',
        );
    }

    /**
     * @return iterable<string, array{string|null, string|null, non-empty-string, non-empty-string|null}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'diaeresis as plain U' => ['Argüello', null, 'ARGUELLO<<ANA', 'ARGUELLO<<ANA'];
        yield 'eñe as NXX, lower case' => ['Núñez', 'García', 'nunxxez<garcia<<ana', 'NUNXXEZ<GARCIA<<ANA'];
        yield 'no surnames' => [null, null, 'ANA', null];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('acceptedOverrides')]
    public function testAcceptsAnotherIcaoSpelling(
        ?string $first,
        ?string $second,
        string $latin,
        ?string $stored,
    ): void {
        $name = new PersonName(
            $first === null ? null : FamilyName::from($first),
            $second === null ? null : FamilyName::from($second),
            GivenName::from('Ana'),
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
        yield 'another name' => ['PEREZ<LOPEZ<<ANA'];
        yield 'second surname dropped' => ['GARCIA<<ANA'];
        yield 'surnames as two identifiers' => ['GARCIA<<LOPEZ<ANA'];
        yield 'surnames swapped' => ['LOPEZ<GARCIA<<ANA'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from('García'),
                FamilyName::from('López'),
                GivenName::from('Ana'),
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['García', 'López', 'Ana', $latin]),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('Argüello'), FamilyName::from('Pérez'), GivenName::from('Ana'));
        $override = new PersonName(
            FamilyName::from('Argüello'),
            FamilyName::from('Pérez'),
            GivenName::from('Ana'),
            IcaoPersonName::from('ARGUELLO<PEREZ<<ANA'),
        );

        self::assertTrue($derived->equals(
            new PersonName(FamilyName::from('Argüello'), FamilyName::from('Pérez'), GivenName::from('Ana')),
        ));
        self::assertFalse($derived->equals($override));
        self::assertFalse($override->equals($derived));
        self::assertFalse($derived->equals(
            new PersonName(FamilyName::from('Arguello'), FamilyName::from('Pérez'), GivenName::from('Ana')),
        ));
        self::assertFalse($derived->equals(
            new PersonName(FamilyName::from('Argüello'), FamilyName::from('Perez'), GivenName::from('Ana')),
        ));
        self::assertFalse($derived->equals(
            new PersonName(FamilyName::from('Argüello'), FamilyName::from('Pérez'), GivenName::from('Anna')),
        ));
        self::assertFalse($derived->equals(GivenName::from('Ana')));
    }

    public function testEqualityTellsNoneOneAndTwoSurnamesApart(): void
    {
        $none = new PersonName(null, null, GivenName::from('Ana'));
        $one = new PersonName(FamilyName::from('Ana'), null, GivenName::from('Ana'));
        $two = new PersonName(FamilyName::from('Ana'), FamilyName::from('Ana'), GivenName::from('Ana'));

        self::assertTrue($none->equals(new PersonName(null, null, GivenName::from('Ana'))));
        self::assertFalse($none->equals($one));
        self::assertFalse($one->equals($none));
        self::assertFalse($one->equals($two));
        self::assertFalse($two->equals($one));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['first' => 'Zaro', 'second' => null, 'given' => 'Margarita', 'effectiveLatin' => 'ZARO<<MARGARITA'],
            new PersonName(FamilyName::from('Zaro'), null, GivenName::from('Margarita'))->jsonSerialize(),
        );
        self::assertSame(
            ['first' => null, 'second' => null, 'given' => 'Juan', 'effectiveLatin' => 'JUAN'],
            new PersonName(null, null, GivenName::from('Juan'))->jsonSerialize(),
        );
    }

    public function testThreePartsOfTheLongestLengthExceedTheLatinFormLimit(): void
    {
        $part = \str_repeat('a', 100);

        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(
                FamilyName::from($part),
                FamilyName::from($part),
                GivenName::from($part),
            ),
            $part,
            'Invalid ICAO personal name (Latin form too long).',
        );
    }
}
