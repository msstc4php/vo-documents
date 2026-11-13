<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\DE;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\DE\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\DE\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/DE.md "Names".
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
        yield 'umlaut' => ['Müller', 'Müller'];
        yield 'sharp s' => ['Groß', 'Groß'];
        yield 'capital sharp s' => ['GROẞ', 'GROẞ'];
        yield 'double name' => ['Müller-Lüdenscheidt', 'Müller-Lüdenscheidt'];
        yield 'particles, spaces collapsed' => ['  von   der Leyen ', 'von der Leyen'];
        yield 'apostrophe' => ["D\u{2019}Angelo", "D'Angelo"];
        yield 'decomposed umlaut composed' => ["Mu\u{0308}ller", 'Müller'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Anna Maria', GivenName::from('Anna  Maria')->value);
        self::assertSame('Hans-Peter', GivenName::from('Hans-Peter')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'Cyrillic' => ['Иван', 'Invalid German given name (invalid character).'];
        yield 'Greek' => ['Δημήτρης', 'Invalid German given name (invalid character).'];
        yield 'digit' => ['Jürgen2', 'Invalid German given name (invalid character).'];
        yield 'trailing hyphen' => ['Hans-', 'Invalid German given name (invalid character).'];
        yield 'fullwidth' => ["\u{FF2A}an", 'Invalid German given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid German given name (0 characters).'];
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
            fn (): FamilyName => FamilyName::from('Мюллер'),
            'Мюллер',
            'Invalid German family name (invalid character).',
        );
    }
}
