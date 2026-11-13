<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\GB;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\GB\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\GB\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\GB\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\LatinPersonNameBuilder;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/GB.md "Names".
 */
#[CoversClass(PersonName::class)]
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
final class PersonNameTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function derivedSpellings(): iterable
    {
        yield 'apostrophe omitted, circumflex dropped' => ["O'Brien", 'Siân', 'OBRIEN<<SIAN'];
        yield 'double-barrelled, two forenames' => ['Smith-Jones', 'Mary Ann', 'SMITH-JONES<<MARY<ANN'];
        yield 'Welsh' => ['ap Rhys', 'Llŷr', 'AP<RHYS<<LLYR'];
        yield 'Irish fada' => ['Ó Súilleabháin', 'Seán', 'O<SUILLEABHAIN<<SEAN'];
        yield 'German umlaut' => ['Müller', 'Jürgen', 'MUELLER<<JUERGEN'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testAPersonWithOneNameHasASurnameOnly(): void
    {
        $name = new PersonName(FamilyName::from('Madonna'), null);

        self::assertSame('MADONNA', $name->latin()->value);
        self::assertSame('Madonna', $name->full());
        self::assertNull($name->given);
    }

    public function testNfcAndFullNameOrder(): void
    {
        $name = new PersonName(FamilyName::from("Ll\u{0079}\u{0302}r"), GivenName::from('Mary Ann'));

        self::assertSame('Llŷr', $name->family->value);
        self::assertSame('Mary Ann Llŷr', $name->full());
    }

    public function testALetterWithoutAnIcaoSpellingIsRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        new PersonName(FamilyName::from('Kĸ'), GivenName::from('Mary'));
    }

    /**
     * @return iterable<string, array{string, string, non-empty-string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'table variant' => ['Müller', 'Jürgen', 'MULLER<<JURGEN', 'MULLER<<JURGEN'];
        yield 'spelling from a foreign document' => ['Müller', 'Jürgen', 'MILLER<<JURGEN', 'MILLER<<JURGEN'];
        yield 'filler for the hyphen, lower case' => [
            'Smith-Jones',
            'Mary',
            'smith<jones<<maree',
            'SMITH<JONES<<MAREE',
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
            FamilyName::from('Smith-Jones'),
            GivenName::from('Mary'),
            IcaoPersonName::from('smith<jones<<mary'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('Smith-Jones'), GivenName::from('Mary'))));
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

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => "O'Brien", 'given' => 'Siân', 'effectiveLatin' => 'OBRIEN<<SIAN'],
            new PersonName(FamilyName::from("O'Brien"), GivenName::from('Siân'))->jsonSerialize(),
        );
        self::assertSame(
            ['family' => 'Madonna', 'given' => null, 'effectiveLatin' => 'MADONNA'],
            new PersonName(FamilyName::from('Madonna'), null)->jsonSerialize(),
        );
    }
}
