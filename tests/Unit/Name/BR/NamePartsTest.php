<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\BR;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\BR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\BR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/BR.md "Names": several surnames and given names per part, particles kept in the
 * case given, the package's Latin default (no civil registry letter set or punctuation is published).
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
        yield 'particle in lower case' => ['da Silva', 'da Silva'];
        yield 'several surnames and particles' => ['dos Santos de Oliveira', 'dos Santos de Oliveira'];
        yield 'agnome' => ['de Souza Júnior', 'de Souza Júnior'];
        yield 'tilde and cedilla' => ['Magalhães Conceição', 'Magalhães Conceição'];
        yield 'apostrophe' => ["D'Ávila", "D'Ávila"];
        yield 'typographic apostrophe' => ["D\u{2019}Ávila", "D'Ávila"];
        yield 'hyphen' => ['Alves-Pereira', 'Alves-Pereira'];
        yield 'spaces collapsed' => ['  da   Costa ', 'da Costa'];
        yield 'decomposed tilde composed' => ["Guimara\u{0303}es", 'Guimarães'];
        yield 'letter case kept' => ['DA SILVA', 'DA SILVA'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWordsAndParticles(): void
    {
        self::assertSame('Maria da Conceição', GivenName::from('Maria  da Conceição')->value);
        self::assertSame('João Pedro', GivenName::from('João Pedro')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidGivenNames(): iterable
    {
        $character = 'Invalid Brazilian given name (invalid character).';

        yield 'digit' => ['João 2', $character];
        yield 'full stop' => ['J. Pedro', $character];
        yield 'slash' => ['Ana/Maria', $character];
        yield 'Cyrillic' => ['Иван', $character];
        yield 'fullwidth' => ["\u{FF2A}oão", 'Invalid Brazilian given name (compatibility character).'];
        yield 'empty' => ['  ', 'Invalid Brazilian given name (0 characters).'];
    }

    #[DataProvider('invalidGivenNames')]
    public function testGivenNameRefusesDigitsOtherScriptsAndSigns(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(fn (): GivenName => GivenName::from($input), $input, $message);
    }

    public function testFamilyNameRefusesAFullStop(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): FamilyName => FamilyName::from('S. Silva'),
            'S. Silva',
            'Invalid Brazilian family name (invalid character).',
        );
    }
}
