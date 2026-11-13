<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\FR;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\FR\AbstractFrenchNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\FR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\FR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/FR.md "Names".
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[CoversClass(AbstractFrenchNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class NamePartsTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validFamilyNames(): iterable
    {
        yield 'acute accent' => ['Hélène', 'Hélène'];
        yield 'cedilla, upper case' => ['FRANÇOIS', 'FRANÇOIS'];
        yield 'ligature oe' => ['Cœur', 'Cœur'];
        yield 'ligature AE' => ['LÆTITIA', 'LÆTITIA'];
        yield 'y with diaeresis' => ['Aÿ', 'Aÿ'];
        yield 'capital Y with diaeresis' => ['AŸ', 'AŸ'];
        yield 'every lower-case letter of the circular' => ['àâäéèêëïîôöùûüÿçæœ', 'àâäéèêëïîôöùûüÿçæœ'];
        yield 'every upper-case letter of the circular' => ['ÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇÆŒ', 'ÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇÆŒ'];
        yield 'particles, spaces collapsed' => ['  de   La Fontaine ', 'de La Fontaine'];
        yield 'apostrophe' => ["D\u{2019}Artagnan", "D'Artagnan"];
        yield 'hyphen' => ['Martin-Dupont', 'Martin-Dupont'];
        yield 'decomposed accent composed' => ["He\u{0301}le\u{0300}ne", 'Hélène'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Marie Hélène', GivenName::from('Marie  Hélène')->value);
        self::assertSame('Jean-Pierre', GivenName::from('Jean-Pierre')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        $invalid = 'Invalid French given name (invalid character).';

        yield 'tilde (Fañch)' => ['Fañch', $invalid];
        yield 'Polish l with stroke' => ['Łukasz', $invalid];
        yield 'sharp s' => ['Grußi', $invalid];
        yield 'ring above' => ['Åsa', $invalid];
        yield 'caron' => ['Šimon', $invalid];
        yield 'acute on a consonant' => ['Ńoé', $invalid];
        yield 'Cyrillic' => ['Иван', $invalid];
        yield 'digit' => ['Jean2', $invalid];
        yield 'double hyphen' => ['Jean--Pierre', $invalid];
        yield 'trailing hyphen' => ['Jean-', $invalid];
        yield 'fullwidth' => ["\u{FF2A}ean", 'Invalid French given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid French given name (0 characters).'];
    }

    #[DataProvider('invalidGivenNames')]
    public function testGivenNameRefusesLettersOutsideTheCircular(string $input, string $message): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage($message);

        GivenName::from($input);
    }

    public function testFamilyNameRefusesForeignDiacritics(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid French family name (invalid character).');

        FamilyName::from('Muñoz');
    }
}
