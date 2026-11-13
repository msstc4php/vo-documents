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
        yield 'Catalan l with middle dot' => ["Ge\u{0140}la", "Ge\u{0140}la"];
        yield 'decomposed tilde composed' => ["Mun\u{0303}oz", 'Muñoz'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
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
        yield 'digit' => ['José2', 'Invalid Spanish given name (invalid character).'];
        yield 'Catalan middle dot U+00B7' => ["Marcel\u{00B7}lí", 'Invalid Spanish given name (invalid character).'];
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
