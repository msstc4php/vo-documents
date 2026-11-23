<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\CA;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CA\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\CA\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CA.md "Names".
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
        yield 'French accents, double-barrelled' => ['Bérubé-Lévesque', 'Bérubé-Lévesque'];
        yield 'apostrophe' => ["D'Amours", "D'Amours"];
        yield 'typographic apostrophe' => ["O\u{2019}Neill", "O'Neill"];
        yield 'particles, spaces collapsed' => ['  de   la Fontaine ', 'de la Fontaine'];
        yield 'cedilla and diaeresis' => ['Françoise-Noël', 'Françoise-Noël'];
        yield 'decomposed acute composed' => ["Gagne\u{0301}", 'Gagné'];
        yield 'letter case kept' => ['MACDONALD', 'MACDONALD'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Marie Ève', GivenName::from('Marie   Ève')->value);
        self::assertSame('Jean-François', GivenName::from('Jean-François')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'digit' => ['Louis 14', 'Invalid Canadian given name (invalid character).'];
        yield 'Cyrillic' => ['Иван', 'Invalid Canadian given name (invalid character).'];
        yield 'Canadian syllabics' => ["\u{1403}\u{14C4}\u{1483}", 'Invalid Canadian given name (invalid character).'];
        yield 'full stop' => ['J.', 'Invalid Canadian given name (invalid character).'];
        yield 'fullwidth' => ["\u{FF2A}ean", 'Invalid Canadian given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid Canadian given name (empty).'];
    }

    #[DataProvider('invalidGivenNames')]
    public function testGivenNameRefusesDigitsOtherScriptsAndSigns(string $input, string $message): void
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
            fn (): FamilyName => FamilyName::from('Тремблей'),
            'Тремблей',
            'Invalid Canadian family name (invalid character).',
        );
    }
}
