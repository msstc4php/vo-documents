<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\ID;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\ID\FullName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/ID.md "Names": Permendagri 73/2022 Art. 4-5 for new records, Art. 8 for the old.
 */
#[CoversClass(FullName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(AbstractLatinNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class FullNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNames(): iterable
    {
        yield 'two words' => ['Siti Nurhaliza', 'Siti Nurhaliza'];
        yield 'one word (Art. 4(2) asks two of a new record; Art. 8 keeps the old)' => ['Suharto', 'Suharto'];
        yield 'three words, spaces collapsed' => ['  Ni   Luh  Putu ', 'Ni Luh Putu'];
        yield 'abbreviated Muhammad with a full stop' => ['Moh. Hatta', 'Moh. Hatta'];
        yield 'initials' => ['M.A. Rahman', 'M.A. Rahman'];
        yield 'single initial' => ['A. Wahid', 'A. Wahid'];
        yield 'hyphen' => ['Putri-Ayu Lestari', 'Putri-Ayu Lestari'];
        yield 'apostrophe, typographic one folded' => ["Ma\u{2019}ruf Amin", "Ma'ruf Amin"];
        yield 'diacritic' => ['José Ximenes', 'José Ximenes'];
        yield 'lower-case particle' => ['Ahmad bin Abdullah', 'Ahmad bin Abdullah'];
        yield 'more than 60 characters (Art. 4(2) is for new records)' => [
            \str_repeat('Abcdefghij ', 6) . 'Abcdefghij',
            \str_repeat('Abcdefghij ', 6) . 'Abcdefghij',
        ];
    }

    #[DataProvider('validNames')]
    public function testIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FullName::from($input)->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNames(): iterable
    {
        $character = 'Invalid Indonesian full name (invalid character).';

        yield 'digit (Art. 5(3)(b))' => ['Budi2 Santoso', $character];
        yield 'comma' => ['Santoso, Budi', $character];
        yield 'leading full stop' => ['.Budi', $character];
        yield 'two full stops' => ['Moh..Hatta', $character];
        yield 'full stop after a space' => ['Moh .Hatta', $character];
        yield 'leading hyphen' => ['-Budi', $character];
        yield 'two hyphens' => ['Budi--Santoso', $character];
        yield 'Arabic script' => ['محمد', $character];
        yield 'Cyrillic' => ['Иван', $character];
        yield 'Han' => ['王', $character];
        yield 'emoji' => ["Budi \u{1F600}", $character];
        yield 'compatibility character, fullwidth' => [
            'Ｂｕｄｉ',
            'Invalid Indonesian full name (compatibility character).',
        ];
        yield 'empty' => ['   ', 'Invalid Indonesian full name (empty).'];
        yield 'over the length cap' => [
            \str_repeat('a', 101),
            'Invalid Indonesian full name (too long).',
        ];
    }

    #[DataProvider('invalidNames')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): FullName => FullName::from($input),
            $input,
            $message,
        );
    }
}
