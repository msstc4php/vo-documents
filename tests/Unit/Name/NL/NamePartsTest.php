<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\NL;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\NL\AbstractDutchNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\NL\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\NL\GivenName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/NL.md "Names" (LO BRP tables 5.2 and 5.3, prefixes of table 36).
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(AbstractDutchNamePart::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class NamePartsTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validFamilyNames(): iterable
    {
        yield 'RvIG specimen, prefix in lower case' => ['de Bruijn', 'de Bruijn'];
        yield 'two prefixes, spaces collapsed' => ['  van   der Berg ', 'van der Berg'];
        yield 'capitalised prefix kept' => ['Van der Berg', 'Van der Berg'];
        yield 'prefix starting with an apostrophe' => ["'t Hart", "'t Hart"];
        yield 'apostrophe prefix after a space' => ["van 't Hof", "van 't Hof"];
        yield 'apostrophe prefix and hyphen' => ["'s-Gravesande", "'s-Gravesande"];
        yield 'right single quotation mark' => ["\u{2019}t Hart", "'t Hart"];
        yield 'prefix ending with an apostrophe' => ["d' Ancona", "d' Ancona"];
        yield 'apostrophe inside' => ["d'Ancona", "d'Ancona"];
        yield 'two family names' => ['Jansen-de Vries', 'Jansen-de Vries'];
        yield 'diaeresis' => ['Hoëvell', 'Hoëvell'];
        yield 'decomposed diaeresis composed' => ["Hoe\u{0308}vell", 'Hoëvell'];
        yield 'letter of table 5.2' => ['Øverby', 'Øverby'];
        yield 'dotted capital I of table 5.3' => ['İnce', 'İnce'];
        yield 'dotless i of table 5.2' => ['Kılıç', 'Kılıç'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Willeke Liselotte', GivenName::from('Willeke  Liselotte')->value);
        self::assertSame('Hendrik-Jan', GivenName::from('Hendrik-Jan')->value);
        self::assertSame('Noël', GivenName::from('Noël')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidFamilyNames(): iterable
    {
        $invalid = 'Invalid Dutch family name (invalid character).';

        yield 'ij ligature, not in table 5.2' => ["\u{0133}sselstein", $invalid];
        yield 'capital IJ ligature' => ["\u{0132}sselstein", $invalid];
        yield 'capital eth, only the small one is listed' => ['Ðorđe', $invalid];
        yield 's with comma below, not listed' => ['Ștefan', $invalid];
        yield 'Cyrillic' => ['Ванде', $invalid];
        yield 'digit' => ['Berg2', $invalid];
        yield 'two apostrophes' => ["d''Ancona", $invalid];
        yield 'apostrophe after a hyphen' => ["Berg-'t Hart", $invalid];
        yield 'trailing apostrophe' => ["Hart'", $invalid];
        yield 'apostrophe only' => ["'", $invalid];
        yield 'apostrophe before a hyphen' => ["d'-Ancona", $invalid];
        yield 'trailing hyphen' => ['Berg-', $invalid];
        yield 'apostrophes on both sides of a space' => ["x' 'y", $invalid];
        yield 'apostrophe prefixes on both sides of a space' => ["d' 't Hart", $invalid];
        $compatibility = 'Invalid Dutch family name (compatibility character).';

        yield 'fullwidth' => ["\u{FF22}erg", $compatibility];
        yield 'n preceded by apostrophe of table 5.2' => ["\u{0149}t", $compatibility];
        yield 'empty' => ['  ', 'Invalid Dutch family name (0 characters).'];
    }

    #[DataProvider('invalidFamilyNames')]
    public function testFamilyNameRefusesCharactersOutsideTheBrpSet(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): FamilyName => FamilyName::from($input),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validGivenNames(): iterable
    {
        yield 'small eth of table 5.2' => ['Sigurðr', 'Sigurðr'];
        yield 'dotless i of table 5.2' => ['Aylın', 'Aylın'];
        yield 'leading apostrophe, as the shared word rule allows' => ["'Anna", "'Anna"];
        yield 'leading right single quotation mark' => ["\u{2019}Anna Marie", "'Anna Marie"];
    }

    #[DataProvider('validGivenNames')]
    public function testGivenNameAcceptsTableLetters(string $input, string $expected): void
    {
        self::assertSame($expected, GivenName::from($input)->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'capital eth, not in table 5.2' => ['ÐORÐE'];
        yield 'small dotted i with dot above, not in table 5.3' => ["i\u{0307}lse"];
        yield 'apostrophe after a hyphen' => ["Anna-'Marie"];
        yield 'two leading apostrophes' => ["''Anna"];
        yield 'trailing apostrophe' => ["Anna'"];
    }

    #[DataProvider('invalidGivenNames')]
    public function testGivenNameRefusesCharactersOutsideTheBrpSet(string $input): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): GivenName => GivenName::from($input),
            $input,
            'Invalid Dutch given name (invalid character).',
        );
    }

    public function testGivenNameRefusesOtherScripts(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): GivenName => GivenName::from('Δημήτρης'),
            'Δημήτρης',
            'Invalid Dutch given name (invalid character).',
        );
    }
}
