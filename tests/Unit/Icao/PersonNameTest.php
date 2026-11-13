<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Icao;

use InvalidArgumentException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonName::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class PersonNameTest extends TestCase
{
    public function testFromPartsBuildsTheCanonicalForm(): void
    {
        $name = PersonName::fromParts('Rimsky-Korsakov', ['Nikolai', 'Andreevich']);

        self::assertSame('RIMSKY-KORSAKOV<<NIKOLAI<ANDREEVICH', $name->value);
        self::assertSame('RIMSKY-KORSAKOV', $name->primaryIdentifier());
        self::assertSame(['NIKOLAI', 'ANDREEVICH'], $name->secondaryIdentifiers());
        self::assertSame('RIMSKY-KORSAKOV, NIKOLAI ANDREEVICH', $name->visualZone());
    }

    public function testFromPartsMapsSpacesToFillers(): void
    {
        $name = PersonName::fromParts('van der Berg', ['Jan']);

        self::assertSame('VAN<DER<BERG<<JAN', $name->value);
        self::assertSame('VAN DER BERG', $name->primaryIdentifier());
        self::assertSame('VAN DER BERG, JAN', $name->visualZone());
    }

    public function testFromPartsNormalisesWhitespace(): void
    {
        self::assertSame(
            'VAN<DER<BERG<<JAN<PIETER',
            PersonName::fromParts("  van\u{00A0}der\tBerg ", [' Jan ', 'Pieter'])->value,
        );
    }

    public function testFromPartsWithoutSecondaryIsAMononym(): void
    {
        self::assertSame('MADONNA', PersonName::fromParts('Madonna')->value);
    }

    public function testMononym(): void
    {
        $name = PersonName::from('MADONNA');

        self::assertSame([], $name->secondaryIdentifiers());
        self::assertSame('MADONNA', $name->primaryIdentifier());
        self::assertSame('MADONNA', $name->visualZone());
    }

    public function testFromNormalisesCaseAndSurroundingWhitespace(): void
    {
        self::assertSame('IVANOV<<IVAN', PersonName::from(' ivanov<<ivan ')->value);
        self::assertSame('VAN<DER<BERG<<JAN', PersonName::from('van der Berg<<Jan')->value);
    }

    public function testEqualityIsByCanonicalValue(): void
    {
        self::assertTrue(PersonName::from('ivanov<<ivan')->equals(PersonName::fromParts('Ivanov', ['Ivan'])));
        self::assertFalse(PersonName::from('IVANOV<<IVAN')->equals(PersonName::from('IVANOV<IVAN')));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function refusedNames(): iterable
    {
        yield 'triple filler' => ['IVANOV<<<IVAN'];
        yield 'diacritic' => ['IVÁNOV'];
        yield 'empty secondary' => ['IVANOV<<'];
        yield 'leading hyphen' => ['-IVANOV'];
        yield 'trailing hyphen' => ['IVANOV-'];
        yield 'leading filler' => ['<<IVAN'];
        yield 'double secondary separator' => ['IVANOV<<IVAN<<IVANOVICH'];
        yield 'hyphen next to filler' => ['IVANOV-<IVAN'];
        yield 'double hyphen' => ['IVANOV--PETROV'];
        yield 'empty' => [''];
        yield 'blank' => ['   '];
        yield 'digits' => ['IVANOV2<<IVAN'];
        yield 'Cyrillic' => ['ИВАНОВ'];
        yield 'apostrophe' => ["O'BRIEN"];
    }

    #[DataProvider('refusedNames')]
    public function testRefusedWithoutEchoingTheInput(string $input): void
    {
        try {
            PersonName::from($input);
            self::fail('Expected exception.');
        } catch (InvalidPersonNameException $e) {
            self::assertSame('Invalid ICAO personal name.', $e->getMessage());
        }
    }

    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function partsWithFillers(): iterable
    {
        yield 'in the primary' => ['Ivanov<<Ivan', []];
        yield 'single in the primary' => ['Van<Der<Berg', ['Jan']];
        yield 'in a secondary' => ['Ivanov', ['Ivan<Ivanovich']];
    }

    /**
     * @param list<string> $secondary
     */
    #[DataProvider('partsWithFillers')]
    public function testFromPartsRefusesFillersInsideAPart(string $primary, array $secondary): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid ICAO personal name.');

        PersonName::fromParts($primary, $secondary);
    }

    public function testMrzFieldOfTheTruncationExampleNameFollowsTheRule(): void
    {
        // §4.2.3.3 a-c print three valid results for this name; ours is a fourth (docs/sources/ICAO.md).
        $field = self::bennelong()->mrzField();

        self::assertSame(39, \strlen($field));
        self::assertMatchesRegularExpression('/^BENNELONG<[A-Z<]*[A-Z]<<D(?:[A-Z<]*[A-Z])?$/', $field);
    }

    public function testFromPartsRefusesAnEmptySecondaryIdentifier(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        PersonName::fromParts('Ivanov', ['Ivan', ' ']);
    }

    public function testTryFromReturnsNullForInvalidInput(): void
    {
        self::assertNull(PersonName::tryFrom('IVANOV<<'));
        self::assertTrue(PersonName::isValid('IVANOV<<IVAN'));
    }

    /**
     * @return iterable<string, array{PersonName, int<2, max>, non-empty-string}>
     */
    public static function mrzFields(): iterable
    {
        // Doc 9303 Part 4 §4.2.3.1-§4.2.3.2, copied from docs/sources/ICAO.md.
        yield '4.2.3.1 a' => [
            PersonName::fromParts('Eriksson', ['Anna', 'Maria']), 39, 'ERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<',
        ];
        yield '4.2.3.1 c hyphen' => [
            PersonName::fromParts('Smith-Jones', ['Susie', 'Margaret']), 39, 'SMITH<JONES<<SUSIE<MARGARET<<<<<<<<<<<<',
        ];
        yield '4.2.3.1 b' => [
            PersonName::fromParts('Heng', ['Deborah', 'Ming', 'Lo']), 39, 'HENG<<DEBORAH<MING<LO<<<<<<<<<<<<<<<<<<',
        ];
        yield '4.2.3.1 d apostrophe' => [
            PersonName::fromParts(new Transliterator()->toLatin("O\u{2019}Connor"), ['Enya', 'Siobhan']),
            39,
            'OCONNOR<<ENYA<SIOBHAN<<<<<<<<<<<<<<<<<<',
        ];
        yield '4.2.3.1 e prefixes' => [
            PersonName::fromParts('Van Der Muellen', ['Martin']), 39, 'VAN<DER<MUELLEN<<MARTIN<<<<<<<<<<<<<<<<',
        ];
        yield '4.2.3.1 e hyphen' => [
            PersonName::fromParts('Al-Basri', ['Huda', 'Muhammad', 'Jawad']),
            39,
            'AL<BASRI<<HUDA<MUHAMMAD<JAWAD<<<<<<<<<<',
        ];
        yield '4.2.3.1 e two primary components' => [
            PersonName::fromParts('Vilarchao Fernandez', ['Jose', 'Ramon']),
            39,
            'VILARCHAO<FERNANDEZ<<JOSE<RAMON<<<<<<<<',
        ];
        yield '4.2.3.1 f two components, no secondary' => [
            PersonName::from('SATRIYA SUDARPA'), 39, 'SATRIYA<SUDARPA<<<<<<<<<<<<<<<<<<<<<<<<',
        ];
        yield '4.2.3.4 exact fit, not truncated' => [
            PersonName::fromParts('Papandropoulous', ['Jonathon', 'Warren', 'Trevor']),
            39,
            'PAPANDROPOULOUS<<JONATHON<WARREN<TREVOR',
        ];
        yield '4.2.3.1 f no secondary' => [
            PersonName::from('ARKFREITH'), 39, 'ARKFREITH<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<',
        ];
        yield '4.2.3.2 a initial' => [
            PersonName::fromParts('Nilavadhanananda', ['Chayapa', 'Dejthamrong', 'Krasuang']),
            39,
            'NILAVADHANANANDA<<CHAYAPA<DEJTHAMRONG<K',
        ];
        yield '4.2.3.2 b component cut' => [
            PersonName::fromParts('Nilavadhanananda', ['Arnpol', 'Petch', 'Charonguang']),
            39,
            'NILAVADHANANANDA<<ARNPOL<PETCH<CHARONGU',
        ];
        // Default width is TD3's 39.
        yield 'exact fit' => [PersonName::from(\str_repeat('A', 39)), 39, \str_repeat('A', 39)];
        // Derived examples from docs/sources/ICAO.md ("Derived examples" table and the procedure's steps 2-4).
        yield 'primary cut, width 39' => [self::bennelong(), 39, 'BENNELONG<WOOLOOMOOLOO<WARRANDYTE<WA<<D'];
        yield 'primary cut, width 20' => [self::bennelong(), 20, 'BENNELONG<WOOLOOM<<D'];
        yield 'hyphenated secondary' => [
            PersonName::fromParts('Ivanov', ['Jean-Pierre']), 39, 'IVANOV<<JEAN<PIERRE<<<<<<<<<<<<<<<<<<<<',
        ];
        yield 'cut landing on a secondary hyphen' => [PersonName::fromParts('Ivanov', ['Jean-Pierre']), 13, 'IVANOV<<JEA<P'];
        yield 'cut landing on a filler' => [PersonName::fromParts('Eriksson', ['Anna', 'Maria']), 15, 'ERIKSSON<<ANN<M'];
        yield 'mononym cut' => [PersonName::from('ARKFREITH'), 5, 'ARKFR'];
        yield 'mononym cut landing on a filler' => [PersonName::from('ANNA<MARIA'), 5, 'ANN<M'];
        yield 'primary cut landing on a filler' => [PersonName::fromParts('Anna Maria', ['Eva']), 8, 'ANN<M<<E'];
        yield 'secondary room after a short primary' => [PersonName::fromParts('Lo', ['Deborah']), 5, 'LO<<D'];
        yield 'secondary component of one letter is skipped over' => [
            PersonName::fromParts('Lo', ['Ann', 'B', 'Carl']), 10, 'LO<<AN<B<C',
        ];
        yield 'every component a single letter' => [PersonName::fromParts('A', ['B', 'C']), 5, 'A<<B<'];
        yield 'primary of initials cut on a filler' => [PersonName::fromParts('A B C', ['D']), 7, 'A<B<<D<'];
        yield 'width 4 keeps << and an initial' => [PersonName::fromParts('Ivanov', ['Ivan']), 4, 'I<<I'];
        yield 'width below the secondary rule' => [PersonName::fromParts('Ivanov', ['Ivan']), 3, 'IVA'];
        yield 'width 2' => [PersonName::from('IVANOV'), 2, 'IV'];
    }

    /**
     * @param int<2, max> $width
     * @param non-empty-string $expected
     */
    #[DataProvider('mrzFields')]
    public function testMrzField(PersonName $name, int $width, string $expected): void
    {
        self::assertSame($expected, $name->mrzField($width));
        self::assertSame($width, \strlen($name->mrzField($width)));
    }

    public function testMrzFieldDefaultsToTd3Width(): void
    {
        self::assertSame('ERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<', PersonName::from('ERIKSSON<<ANNA<MARIA')->mrzField());
    }

    public function testMrzFieldRefusesAWidthBelowTwo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Reason: the int<2, max> contract is what this test violates on purpose.
        // @phpstan-ignore argument.type
        PersonName::from('IVANOV')->mrzField(1);
    }

    public function testTheLongestAcceptedFormHasMaxLengthCharacters(): void
    {
        $primary = \str_repeat('A', 127);
        $secondary = \str_repeat('B', PersonName::MAX_LENGTH - 129);
        $longest = $primary . '<<' . $secondary;

        self::assertSame(PersonName::MAX_LENGTH, \strlen($longest));
        self::assertSame($longest, PersonName::from($longest)->value);
        self::assertFalse(PersonName::isValid($longest . 'B'));
    }

    public function testTooLongANameIsRefusedWithoutEchoingIt(): void
    {
        try {
            PersonName::fromParts(\str_repeat('Q', PersonName::MAX_LENGTH), ['X']);
            self::fail('A name over the limit was accepted.');
        } catch (InvalidPersonNameException $exception) {
            self::assertStringNotContainsString('QQQ', $exception->getMessage());
        }
    }

    private static function bennelong(): PersonName
    {
        return PersonName::fromParts('Bennelong Wooloomooloo Warrandyte Warnambool', ['Dingo', 'Potoroo']);
    }
}
