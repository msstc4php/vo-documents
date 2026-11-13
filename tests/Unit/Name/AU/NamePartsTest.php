<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\AU;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AU\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\AU\GivenName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/AU.md "Names".
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class NamePartsTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    public function testGivenNameWithDiaeresisTransliteratesByTheMultinationalTable(): void
    {
        $given = GivenName::from('Zoë');

        self::assertSame('Zoë', $given->value);
        self::assertSame('ZOE', new Transliterator()->toLatin($given->value));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validFamilyNames(): iterable
    {
        yield 'plain' => ['Citizen', 'Citizen'];
        yield 'double-barrelled' => ['Smith-Jones', 'Smith-Jones'];
        yield 'apostrophe' => ["O'Brien", "O'Brien"];
        yield 'right single quotation mark stored as apostrophe' => ["O\u{2019}Brien", "O'Brien"];
        yield 'particles, spaces collapsed' => ['  van   der Berg ', 'van der Berg'];
        yield 'decomposed umlaut composed' => ["Mu\u{0308}ller", 'Müller'];
        yield 'case kept' => ['McDONALD', 'McDONALD'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Joan Catherine', GivenName::from('Joan  Catherine')->value);
        self::assertSame('Mary-Anne', GivenName::from('Mary-Anne')->value);
        self::assertSame("D'Arcy", GivenName::from("D'Arcy")->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'Cyrillic' => ['Иван', 'Invalid Australian given name (invalid character).'];
        yield 'Latin with a Cyrillic look-alike' => ['Jоhn', 'Invalid Australian given name (invalid character).'];
        yield 'digit' => ['John2', 'Invalid Australian given name (invalid character).'];
        yield 'leading hyphen' => ['-John', 'Invalid Australian given name (invalid character).'];
        yield 'two separators in a row' => ["Mary-'Anne", 'Invalid Australian given name (invalid character).'];
        yield 'empty' => ['', 'Invalid Australian given name (0 characters).'];
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
            fn (): FamilyName => FamilyName::from('Иванов'),
            'Иванов',
            'Invalid Australian family name (invalid character).',
        );
    }
}
