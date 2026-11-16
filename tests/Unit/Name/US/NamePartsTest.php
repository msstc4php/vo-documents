<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\US;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\US\AbstractAmericanNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\US\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\US\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\US\MiddleName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/US.md "Names".
 */
#[CoversClass(AbstractAmericanNamePart::class)]
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(MiddleName::class)]
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
        yield 'apostrophe' => ["O'Brien", "O'Brien"];
        yield 'typographic apostrophe' => ["O\u{2019}Brien", "O'Brien"];
        yield 'hyphenated, no spaces around the hyphen' => ['Bertram-Wilberforce', 'Bertram-Wilberforce'];
        yield 'particles, spaces collapsed' => ['  de   la Cruz ', 'de la Cruz'];
        yield 'diacritics kept in the national form' => ['Peña', 'Peña'];
        yield 'decomposed accent composed' => ["Pen\u{0303}a", 'Peña'];
        yield 'okina stored as an apostrophe' => ["Kalaniana\u{02BB}ole", "Kalaniana'ole"];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenAndMiddleNamesMayBeCompound(): void
    {
        self::assertSame('Mary Ann', GivenName::from('Mary  Ann')->value);
        self::assertSame("Ku'ulei", GivenName::from("Ku\u{02BB}ulei")->value);
        self::assertSame('Lee Ann', MiddleName::from(' Lee Ann ')->value);
        self::assertSame('José', MiddleName::from("Jose\u{0301}")->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'Cyrillic' => ['Иван', 'Invalid American given name (invalid character).'];
        yield 'Greek' => ['Γιώργος', 'Invalid American given name (invalid character).'];
        yield 'Han' => ['明', 'Invalid American given name (invalid character).'];
        yield 'numeral (spelled out in a passport)' => ['John 7', 'Invalid American given name (invalid character).'];
        yield 'period of an initial' => ['J.', 'Invalid American given name (invalid character).'];
        yield 'hyphen with spaces' => ['Mary - Ann', 'Invalid American given name (invalid character).'];
        yield 'fullwidth' => ["\u{FF2A}ohn", 'Invalid American given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid American given name (0 characters).'];
        yield 'too long' => [\str_repeat('a', 101), 'Invalid American given name (101 characters).'];
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

    public function testFamilyAndMiddleNamesHaveTheirOwnLabels(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): FamilyName => FamilyName::from('Смит'),
            'Смит',
            'Invalid American family name (invalid character).',
        );
        self::assertNameRefusedWithoutEcho(
            fn (): MiddleName => MiddleName::from('Lee2'),
            'Lee2',
            'Invalid American middle name (invalid character).',
        );
    }
}
