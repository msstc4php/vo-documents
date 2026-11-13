<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\GB;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\GB\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\GB\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/GB.md "Names".
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
        yield 'apostrophe' => ["O'Brien", "O'Brien"];
        yield 'typographic apostrophe' => ["O\u{2019}Brien", "O'Brien"];
        yield 'double-barrelled' => ['Smith-Jones', 'Smith-Jones'];
        yield 'Welsh ap, spaces collapsed' => ['  ap   Rhys ', 'ap Rhys'];
        yield 'Welsh circumflex' => ['Llŷr', 'Llŷr'];
        yield 'decomposed circumflex composed' => ["Ll\u{0079}\u{0302}r", 'Llŷr'];
        yield 'Irish fada' => ['Ó Súilleabháin', 'Ó Súilleabháin'];
        yield 'letter case kept' => ['MACDONALD', 'MACDONALD'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Mary Ann', GivenName::from('Mary   Ann')->value);
        self::assertSame('Siân', GivenName::from('Siân')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'digit (HMPO spells numbers out)' => ['Thomas 7', 'Invalid British given name (invalid character).'];
        yield 'Cyrillic' => ['Иван', 'Invalid British given name (invalid character).'];
        yield 'Greek' => ['Δημήτρης', 'Invalid British given name (invalid character).'];
        yield 'full stop' => ['J.', 'Invalid British given name (invalid character).'];
        yield 'leading apostrophe' => ["'Mary", 'Invalid British given name (invalid character).'];
        yield 'fullwidth' => ["\u{FF2A}ohn", 'Invalid British given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid British given name (0 characters).'];
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
            fn (): FamilyName => FamilyName::from('Смит'),
            'Смит',
            'Invalid British family name (invalid character).',
        );
    }
}
