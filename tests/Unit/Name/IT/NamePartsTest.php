<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\IT;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IT\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\IT\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IT.md "Names".
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
        yield 'grave accent' => ['Nicolò', 'Nicolò'];
        yield 'final accented a' => ['Mazzà', 'Mazzà'];
        yield 'elided particle' => ["D\u{2019}Angelo", "D'Angelo"];
        yield 'particle, spaces collapsed' => ['  De   Luca ', 'De Luca'];
        yield 'lower-case particle kept' => ['di Pietro', 'di Pietro'];
        yield 'double surname' => ['Rossi-Bianchi', 'Rossi-Bianchi'];
        yield 'decomposed accent composed' => ["Nicolo\u{0300}", 'Nicolò'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Maria Grazia', GivenName::from('Maria  Grazia')->value);
        self::assertSame('Gian-Luca', GivenName::from('Gian-Luca')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        yield 'Cyrillic' => ['Иван', 'Invalid Italian given name (invalid character).'];
        yield 'Greek' => ['Δημήτρης', 'Invalid Italian given name (invalid character).'];
        yield 'digit' => ['Giuseppe2', 'Invalid Italian given name (invalid character).'];
        yield 'trailing hyphen' => ['Gian-', 'Invalid Italian given name (invalid character).'];
        yield 'fullwidth' => ["\u{FF27}ino", 'Invalid Italian given name (compatibility character).'];
        yield 'comma between given names' => ['Maria, Luisa', 'Invalid Italian given name (invalid character).'];
        yield 'empty' => ['  ', 'Invalid Italian given name (0 characters).'];
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
            fn (): FamilyName => FamilyName::from('Росси'),
            'Росси',
            'Invalid Italian family name (invalid character).',
        );
    }
}
