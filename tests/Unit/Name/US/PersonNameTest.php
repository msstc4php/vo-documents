<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\US;

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
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;
use MSSTC4PHP\ValueObject\Documents\Name\US\AbstractAmericanNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\US\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\US\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\US\MiddleName;
use MSSTC4PHP\ValueObject\Documents\Name\US\NameSuffix;
use MSSTC4PHP\ValueObject\Documents\Name\US\PersonName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/US.md "Names" and "Name suffix".
 */
#[CoversClass(PersonName::class)]
#[CoversClass(AbstractLatinPersonName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(AbstractAmericanNamePart::class)]
#[UsesClass(LatinPersonNameBuilder::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(MiddleName::class)]
#[UsesClass(NameSuffix::class)]
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
        'Invalid American person name (the Latin form is not a transliteration of the name).';

    /**
     * @return iterable<string, array{string, string|null, string|null, NameSuffix|null, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'one name (8 FAM 403.1-3(D))' => ['Teller', null, null, null, 'TELLER'];
        yield 'first name only' => ['Smith', 'John', null, null, 'SMITH<<JOHN'];
        yield 'with middle name' => ['Smith', 'John', 'Michael', null, 'SMITH<<JOHN<MICHAEL'];
        yield 'with suffix' => ['Smith', 'John', null, NameSuffix::Junior, 'SMITH<<JOHN<JR'];
        yield 'middle name and suffix' => ['Smith', 'John', 'Michael', NameSuffix::II, 'SMITH<<JOHN<MICHAEL<II'];
        yield 'ordinal suffix as a Roman numeral' => ["O'Brien", 'José Luis', null, NameSuffix::Third,
            'OBRIEN<<JOSE<LUIS<III'];
        yield 'diacritics dropped, hyphen kept' => ['Peña-Ruiz', 'Zoë', null, null, 'PENA-RUIZ<<ZOE'];
        yield 'particles' => ['de la Cruz', 'Ana', 'María', null, 'DE<LA<CRUZ<<ANA<MARIA'];
        yield 'okina omitted like an apostrophe' => ["Kalaniana\u{02BB}ole", "Ku\u{02BB}ulei", null, null,
            'KALANIANAOLE<<KUULEI'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(
        string $family,
        ?string $given,
        ?string $middle,
        ?NameSuffix $suffix,
        string $expected,
    ): void {
        $name = new PersonName(
            FamilyName::from($family),
            $given === null ? null : GivenName::from($given),
            $middle === null ? null : MiddleName::from($middle),
            $suffix,
        );

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(
            FamilyName::from('Smith'),
            GivenName::from("Jose\u{0301}"),
            MiddleName::from('Michael'),
            NameSuffix::Junior,
        );

        self::assertSame('José', $name->given?->value);
        self::assertSame('José Michael Smith JR', $name->full());
        self::assertSame('John Smith', new PersonName(FamilyName::from('Smith'), GivenName::from('John'))->full());
        self::assertSame('Teller', new PersonName(FamilyName::from('Teller'), null)->full());
        self::assertSame(
            'John Smith 3RD',
            new PersonName(FamilyName::from('Smith'), GivenName::from('John'), null, NameSuffix::Third)->full(),
        );
    }

    /**
     * @return iterable<string, array{string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'diaeresis crossed out (8 FAM)' => ['Möller', 'moller<<anna<jr', 'MOLLER<<ANNA<JR'];
        yield 'eñe as NXX' => ['Peña', 'PENXXA<<ANNA<JR', 'PENXXA<<ANNA<JR'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('acceptedOverrides')]
    public function testAcceptsAnotherIcaoSpelling(string $family, string $latin, string $stored): void
    {
        $name = new PersonName(
            FamilyName::from($family),
            GivenName::from('Anna'),
            null,
            NameSuffix::Junior,
            IcaoPersonName::from($latin),
        );

        self::assertSame($stored, $name->latin()->value);
        self::assertSame($stored, $name->latinOverride()?->value);
    }

    public function testOneNameTakesAnOverrideOfTheSameName(): void
    {
        $derived = new PersonName(FamilyName::from('Möller'), null, null, null, IcaoPersonName::from('moeller'));
        $crossedOut = new PersonName(FamilyName::from('Möller'), null, null, null, IcaoPersonName::from('moller'));

        self::assertNull($derived->latinOverride());
        self::assertSame('MOLLER', $crossedOut->latin()->value);
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(
                FamilyName::from('Möller'),
                null,
                null,
                null,
                IcaoPersonName::from('MOLLER<<ANNA'),
            ),
            'Möller MOLLER ANNA',
            self::NOT_A_TRANSLITERATION,
        );
    }

    /**
     * @return iterable<string, array{MiddleName|null, NameSuffix|null, non-empty-string}>
     */
    public static function partsWithoutAGivenName(): iterable
    {
        yield 'middle name' => [MiddleName::from('Lee'), null, 'middle name without a given name'];
        yield 'suffix' => [null, NameSuffix::Junior, 'suffix without a given name'];
        yield 'both' => [MiddleName::from('Lee'), NameSuffix::Junior, 'middle name without a given name'];
    }

    #[DataProvider('partsWithoutAGivenName')]
    public function testRefusesAMiddleNameOrSuffixWithoutAGivenName(
        ?MiddleName $middle,
        ?NameSuffix $suffix,
        string $reason,
    ): void {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(FamilyName::from('Teller'), null, $middle, $suffix),
            'Teller Lee',
            'Invalid American person name (' . $reason . ').',
        );
    }

    public function testAnOverrideThatIsTheDerivedFormIsNotKept(): void
    {
        $name = new PersonName(
            FamilyName::from('Möller'),
            GivenName::from('Anna'),
            null,
            null,
            IcaoPersonName::from('moeller<<anna'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Möller'), GivenName::from('Anna'))));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function foreignOverrides(): iterable
    {
        yield 'another name' => ['JONES<<ANNA<LEE<II'];
        yield 'suffix dropped' => ['PENA<<ANNA<LEE'];
        yield 'Roman numeral of another generation' => ['PENA<<ANNA<LEE<III'];
        yield 'middle name dropped' => ['PENA<<ANNA<II'];
        yield 'eñe spelt NY' => ['PENYA<<ANNA<LEE<II'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('foreignOverrides')]
    public function testRefusesAnOverrideThatIsNotATransliteration(string $latin): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): PersonName => new PersonName(
                FamilyName::from('Peña'),
                GivenName::from('Anna'),
                MiddleName::from('Lee'),
                NameSuffix::Second,
                IcaoPersonName::from($latin),
            ),
            \implode(' ', ['Peña', 'Anna', 'Lee', $latin]),
            self::NOT_A_TRANSLITERATION,
        );
    }

    public function testEqualityComparesEveryPartAndTheOverride(): void
    {
        $name = static fn (
            string $family = 'Möller',
            string $given = 'Anna',
            ?string $middle = 'Lee',
            ?NameSuffix $suffix = NameSuffix::Junior,
            ?string $latin = null,
        ): PersonName => new PersonName(
            FamilyName::from($family),
            GivenName::from($given),
            $middle === null ? null : MiddleName::from($middle),
            $suffix,
            $latin === null ? null : IcaoPersonName::from($latin),
        );

        self::assertTrue($name()->equals($name()));
        self::assertFalse($name()->equals($name(family: 'Moller')));
        self::assertFalse($name()->equals($name(given: 'Ann')));
        $oneName = new PersonName(FamilyName::from('Möller'), null);
        self::assertTrue($oneName->equals(new PersonName(FamilyName::from('Möller'), null)));
        self::assertFalse($oneName->equals($name(middle: null, suffix: null)));
        self::assertFalse($name(middle: null, suffix: null)->equals($oneName));
        self::assertFalse($name()->equals($name(middle: 'Leigh')));
        self::assertFalse($name()->equals($name(middle: null)));
        self::assertFalse($name(middle: null)->equals($name()));
        self::assertFalse($name()->equals($name(suffix: null)));
        self::assertFalse($name()->equals($name(suffix: NameSuffix::Senior)));
        self::assertFalse($name(suffix: NameSuffix::II)->equals($name(suffix: NameSuffix::Second)));
        self::assertFalse($name()->equals($name(latin: 'MOLLER<<ANNA<LEE<JR')));
        self::assertTrue($name(latin: 'MOLLER<<ANNA<LEE<JR')->equals($name(latin: 'moller<<anna<lee<jr')));
        self::assertFalse($name()->equals(GivenName::from('Anna')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'Teller', 'given' => null, 'middle' => null, 'suffix' => null, 'latin' => 'TELLER'],
            new PersonName(FamilyName::from('Teller'), null)->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Smith', 'given' => 'John', 'middle' => null, 'suffix' => null,
                'latin' => 'SMITH<<JOHN'],
            new PersonName(FamilyName::from('Smith'), GivenName::from('John'))->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Smith', 'given' => 'John', 'middle' => 'Lee', 'suffix' => '2ND',
                'latin' => 'SMITH<<JOHN<LEE<II'],
            new PersonName(
                FamilyName::from('Smith'),
                GivenName::from('John'),
                MiddleName::from('Lee'),
                NameSuffix::Second,
            )->jsonSerialize(),
        );
    }

    public function testThreePartsOfTheLongestLengthExceedTheLatinFormLimit(): void
    {
        $part = \str_repeat('a', 100);

        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(
                FamilyName::from($part),
                GivenName::from($part),
                MiddleName::from($part),
            ),
            $part,
            'Invalid ICAO personal name (Latin form too long).',
        );
    }
}
