<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\TR;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\TR\AbstractTurkishNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\TR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\TR\GivenName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/TR.md "Names".
 */
#[CoversClass(AbstractTurkishNamePart::class)]
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
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validFamilyNames(): iterable
    {
        yield 'Turkish letters' => ['Öztürk', 'Öztürk'];
        yield 'dotless and dotted I kept as typed' => ['Işıkİnci', 'Işıkİnci'];
        yield 'upper case kept' => ['ÇAĞLAYAN', 'ÇAĞLAYAN'];
        yield 'two surnames, spaces collapsed' => ['  Yılmaz   Kaya ', 'Yılmaz Kaya'];
        yield 'circumflex' => ['Kâhya', 'Kâhya'];
        yield 'decomposed letters composed' => ["O\u{0308}zgu\u{0308}r S\u{0327}en", 'Özgür Şen'];
        yield 'decomposed dotted capital I composed' => ["I\u{0307}nce", 'İnce'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testDottedAndDotlessIAreDifferentValues(): void
    {
        $values = \array_map(
            static fn (string $name): string => GivenName::from($name)->value,
            ['Irmak', 'İrmak', 'ırmak', 'irmak'],
        );

        self::assertSame(['Irmak', 'İrmak', 'ırmak', 'irmak'], $values);
        self::assertFalse(GivenName::from('Işık')->equals(GivenName::from('Isik')));
        self::assertFalse(GivenName::from('İnci')->equals(GivenName::from('Inci')));
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Ahmet Can', GivenName::from('Ahmet  Can')->value);
        self::assertSame('Âdem', GivenName::from('Âdem')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'Q is not a Turkish letter' => ['Qasım', 'Invalid Turkish given name (invalid character).'];
        yield 'W is not a Turkish letter' => ['Welat', 'Invalid Turkish given name (invalid character).'];
        yield 'X is not a Turkish letter' => ['Xebat', 'Invalid Turkish given name (invalid character).'];
        yield 'lower-cased İ (i and combining dot)' => [
            "i\u{0307}nci",
            'Invalid Turkish given name (invalid character).',
        ];
        yield 'letter of another Latin alphabet' => ['Jürgenß', 'Invalid Turkish given name (invalid character).'];
        yield 'hyphen' => ['Ahmet-Can', 'Invalid Turkish given name (invalid character).'];
        yield 'apostrophe' => ["O'Neil", 'Invalid Turkish given name (invalid character).'];
        yield 'Cyrillic' => ['Иван', 'Invalid Turkish given name (invalid character).'];
        yield 'digit' => ['Ayşe2', 'Invalid Turkish given name (invalid character).'];
        yield 'fullwidth' => ["\u{FF21}yşe", 'Invalid Turkish given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid Turkish given name (0 characters).'];
    }

    #[DataProvider('invalidGivenNames')]
    public function testGivenNameRefusesOtherLettersAndSigns(string $input, string $message): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage($message);

        GivenName::from($input);
    }

    public function testFamilyNameRefusesALetterOutsideTheAlphabet(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid Turkish family name (invalid character).');

        FamilyName::from('Wagner');
    }
}
