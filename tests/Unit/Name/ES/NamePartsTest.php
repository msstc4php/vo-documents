<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\ES;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\ES\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\ES\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/ES.md "Names".
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
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
        yield 'eñe' => ['Muñoz', 'Muñoz'];
        yield 'diaeresis' => ['Argüelles', 'Argüelles'];
        yield 'acute accent' => ['Martínez', 'Martínez'];
        yield 'lower-case particles kept, spaces collapsed' => ['  de   la Fuente ', 'de la Fuente'];
        yield 'contracted particle' => ['del Río', 'del Río'];
        yield 'Catalan apostrophe' => ["d\u{2019}Ors", "d'Ors"];
        yield 'hyphenated' => ['Sánchez-Vicario', 'Sánchez-Vicario'];
        yield 'Catalan l with middle dot U+0140 stored as its NFKC form' => ["Ge\u{0140}la", "Gel\u{00B7}la"];
        yield 'Catalan L with middle dot U+013F stored as its NFKC form' => ["PARA\u{013F}LEL", "PARAL\u{00B7}LEL"];
        yield 'Catalan middle dot U+00B7 between two l' => ["Gel\u{00B7}la", "Gel\u{00B7}la"];
        yield 'middle dot between upper and lower case' => ["Gel\u{00B7}La", "Gel\u{00B7}La"];
        yield 'modifier letter apostrophe' => ["d\u{02BC}Ors", "d'Ors"];
        yield 'decomposed tilde composed' => ["Mun\u{0303}oz", 'Muñoz'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameAcceptsTheCatalanMiddleDot(): void
    {
        self::assertSame("Marcel\u{00B7}lí", GivenName::from("Marcel\u{00B7}lí")->value);
        self::assertTrue(GivenName::from("Marce\u{0140}lí")->equals(GivenName::from("Marcel\u{00B7}lí")));
    }

    public function testGivenNameMayBeCompound(): void
    {
        self::assertSame('María José', GivenName::from('María  José')->value);
        self::assertSame('Josep-Lluís', GivenName::from('Josep-Lluís')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'Cyrillic' => ['Хосе', 'Invalid Spanish given name (invalid character).'];
        yield 'Arabic' => ['محمد', 'Invalid Spanish given name (invalid character).'];
        yield 'Greek' => ['Ιωσήφ', 'Invalid Spanish given name (invalid character).'];
        yield 'digit' => ['José2', 'Invalid Spanish given name (invalid character).'];
        yield 'middle dot between other letters' => ["Joa\u{00B7}na", 'Invalid Spanish given name (invalid character).'];
        yield 'middle dot after l only' => ["Marcel\u{00B7}a", 'Invalid Spanish given name (invalid character).'];
        yield 'middle dot before l only' => ["Mare\u{00B7}la", 'Invalid Spanish given name (invalid character).'];
        yield 'leading middle dot' => ["\u{00B7}llum", 'Invalid Spanish given name (invalid character).'];
        yield 'trailing middle dot' => ["Marcel\u{00B7}", 'Invalid Spanish given name (invalid character).'];
        yield 'two middle dots' => ["Marcel\u{00B7}\u{00B7}lí", 'Invalid Spanish given name (invalid character).'];
        yield 'middle dot next to a space' => ["Marcel \u{00B7}lí", 'Invalid Spanish given name (invalid character).'];
        yield 'l with middle dot before another letter' => ["Ge\u{0140}a", 'Invalid Spanish given name (invalid character).'];
        yield 'fullwidth' => ["\u{FF2A}osé", 'Invalid Spanish given name (compatibility character).'];
        yield 'ordinal indicator' => ["Mar\u{00AA}", 'Invalid Spanish given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid Spanish given name (0 characters).'];
    }

    #[DataProvider('invalidGivenNames')]
    public function testGivenNameRefusesOtherScriptsAndSigns(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): GivenName => GivenName::from($input),
            $input,
            $message,
        );
    }

    public function testFamilyNameRefusesCyrillic(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): FamilyName => FamilyName::from('Гарсия'),
            'Гарсия',
            'Invalid Spanish family name (invalid character).',
        );
    }
}
