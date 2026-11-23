<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\BR;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\BR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\BR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\BR\PersonName;
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
 * Expectations follow docs/sources/BR.md "Names".
 */
#[CoversClass(PersonName::class)]
#[UsesClass(AbstractLatinPersonName::class)]
#[UsesClass(AbstractFamilyGivenPersonName::class)]
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
        yield 'particle in lower case' => ['da Silva', 'João', 'DA<SILVA<<JOAO'];
        yield 'several surnames, cedilla and tilde' => [
            'dos Santos Magalhães',
            'Maria da Conceição',
            'DOS<SANTOS<MAGALHAES<<MARIA<DA<CONCEICAO',
        ];
        yield 'apostrophe omitted' => ["D'Ávila", 'Ana', 'DAVILA<<ANA'];
        yield 'hyphen kept' => ['Alves-Pereira', 'José', 'ALVES-PEREIRA<<JOSE'];
        yield 'agnome' => ['de Souza Júnior', 'Antônio', 'DE<SOUZA<JUNIOR<<ANTONIO'];
    }

    #[DataProvider('derivedSpellings')]
    public function testDerivesTheLatinForm(string $family, string $given, string $expected): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given));

        self::assertSame($expected, $name->latin()->value);
        self::assertNull($name->latinOverride());
    }

    public function testFullNameHasTheGivenNamesFirst(): void
    {
        $name = new PersonName(FamilyName::from('da Silva'), GivenName::from('João Pedro'));

        self::assertSame('João Pedro da Silva', $name->full());
        self::assertSame('da Silva', $name->family->value);
        self::assertSame('João Pedro', $name->given->value);
    }

    /**
     * The Polícia Federal's SINPA drops the particles and then reduces the penultimate word to one letter when a field
     * exceeds 30 characters; "a abreviação não configura erro no passaporte".
     *
     * @return iterable<string, array{string, string, non-empty-string}>
     */
    public static function acceptedOverrides(): iterable
    {
        yield 'particles dropped' => ['dos Santos de Oliveira', 'Ana', 'SANTOS<OLIVEIRA<<ANA'];
        yield 'penultimate word abbreviated' => [
            'Gonçalves Albuquerque Cavalcanti Bragança',
            'Pedro',
            'GONCALVES<ALBUQUERQUE<C<BRAGANCA<<PEDRO',
        ];
        yield 'another spelling' => ['Conceição', 'Ana', 'CONCEIZAO<<ANA'];
    }

    /**
     * @param non-empty-string $latin
     */
    #[DataProvider('acceptedOverrides')]
    public function testKeepsAnyIcaoSpellingAsTheOverride(string $family, string $given, string $latin): void
    {
        $name = new PersonName(FamilyName::from($family), GivenName::from($given), IcaoPersonName::from($latin));

        self::assertSame($latin, $name->latin()->value);
        self::assertSame($latin, $name->latinOverride()?->value);
    }

    public function testAnOverrideThatIsTheDerivedFormIsNotKept(): void
    {
        $name = new PersonName(
            FamilyName::from('da Silva'),
            GivenName::from('João'),
            IcaoPersonName::from('da<silva<<joao'),
        );

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(new PersonName(FamilyName::from('da Silva'), GivenName::from('João'))));
    }

    public function testALetterWithoutAnIcaoSpellingNeedsAnOverride(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => new PersonName(FamilyName::from('Kĸ'), GivenName::from('Ana')),
            'Kĸ Ana',
            'Invalid personal name (no ICAO transliteration for a character).',
        );

        $name = new PersonName(FamilyName::from('Kĸ'), GivenName::from('Ana'), IcaoPersonName::from('KK<<ANA'));
        self::assertSame('KK<<ANA', $name->latin()->value);
    }

    public function testEqualityComparesPartsAndOverride(): void
    {
        $derived = new PersonName(FamilyName::from('da Silva'), GivenName::from('João'));
        $override = new PersonName(
            FamilyName::from('da Silva'),
            GivenName::from('João'),
            IcaoPersonName::from('SILVA<<JOAO'),
        );

        self::assertTrue($derived->equals(new PersonName(FamilyName::from('da Silva'), GivenName::from('João'))));
        self::assertFalse($derived->equals($override));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('Da Silva'), GivenName::from('João'))));
        self::assertFalse($derived->equals(new PersonName(FamilyName::from('da Silva'), GivenName::from('Joao'))));
        self::assertFalse($derived->equals(GivenName::from('João')));
    }

    public function testJsonViewShowsTheEffectiveLatinForm(): void
    {
        self::assertSame(
            ['family' => 'da Silva', 'given' => 'João', 'latin' => 'DA<SILVA<<JOAO'],
            new PersonName(FamilyName::from('da Silva'), GivenName::from('João'))->jsonSerialize(),
        );
    }
}
