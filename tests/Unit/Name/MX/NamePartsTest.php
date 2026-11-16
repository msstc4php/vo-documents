<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\MX;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\MX\AbstractMexicanNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\MX\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\MX\GivenName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/MX.md "Names" (examples of the RENAPO Reglas, 5.1).
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[CoversClass(AbstractMexicanNamePart::class)]
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
        yield 'eñe' => ['Garduño', 'Garduño'];
        yield 'diaeresis' => ['Argüello', 'Argüello'];
        yield 'lower-case particles kept, spaces collapsed' => ['  de   la Cruz ', 'de la Cruz'];
        yield 'compound surname of two words' => ['Riva Palacio', 'Riva Palacio'];
        yield 'hyphenated' => ['López-Castillo', 'López-Castillo'];
        yield 'apostrophe' => ["O\u{2019}Hara", "O'Hara"];
        yield 'Nahuatl surname' => ['Zitlalpopoca', 'Zitlalpopoca'];
        yield 'slash between words (Reglas 1.4)' => ['D/Amico', 'D/Amico'];
        yield 'full stop between letters' => ['St.Clair', 'St.Clair'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNamesAreOnePart(): void
    {
        self::assertSame('María Guadalupe', GivenName::from('María  Guadalupe')->value);
        self::assertSame('Lucero Beatriz Alondra', GivenName::from('Lucero Beatriz Alondra')->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function abbreviatedGivenNames(): iterable
    {
        yield 'Ma. (Reglas 1.3)' => ['Ma. Guadalupe'];
        yield 'M.' => ['M. Luisa'];
        yield 'J. alone' => ['J.'];
        yield 'initials' => ['M.A.'];
        yield 'slash' => ['Juan/José'];
    }

    #[DataProvider('abbreviatedGivenNames')]
    public function testGivenNameKeepsRenapoPunctuationAsTyped(string $input): void
    {
        self::assertSame($input, GivenName::from($input)->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidParts(): iterable
    {
        yield 'Cyrillic' => ['Хосе', 'Invalid Mexican given name (invalid character).'];
        yield 'digit' => ['José2', 'Invalid Mexican given name (invalid character).'];
        yield 'leading full stop' => ['.Juan', 'Invalid Mexican given name (invalid character).'];
        yield 'two full stops' => ['Ma..Luisa', 'Invalid Mexican given name (invalid character).'];
        yield 'full stop after a space' => ['Ma .Luisa', 'Invalid Mexican given name (invalid character).'];
        yield 'trailing slash' => ['Juan/', 'Invalid Mexican given name (invalid character).'];
        yield 'two slashes' => ['Juan//José', 'Invalid Mexican given name (invalid character).'];
        yield 'other punctuation' => ['Juan, José', 'Invalid Mexican given name (invalid character).'];
        yield 'empty' => ['  ', 'Invalid Mexican given name (0 characters).'];
    }

    #[DataProvider('invalidParts')]
    public function testGivenNameRefusesOtherScriptsAndSigns(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): GivenName => GivenName::from($input),
            $input,
            $message,
        );
    }

    public function testFamilyNameRefusesALeadingSlash(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): FamilyName => FamilyName::from('/Amico'),
            '/Amico',
            'Invalid Mexican family name (invalid character).',
        );
    }
}
