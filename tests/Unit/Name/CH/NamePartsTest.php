<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\CH;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CH\AbstractSwissNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CH\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\CH\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CH.md "Names" (character set ISO 8859-1 + Latin Extended-A).
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(AbstractSwissNamePart::class)]
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
        yield 'fedpol specimen, two words' => ['Schweizer Sample', 'Schweizer Sample'];
        yield 'German umlaut' => ['Müller', 'Müller'];
        yield 'decomposed umlaut composed' => ["Mu\u{0308}ller", 'Müller'];
        yield 'hyphenated (Allianzname)' => ['Müller-Brunner', 'Müller-Brunner'];
        yield 'particle in lower case, spaces collapsed' => ['  de   Weck ', 'de Weck'];
        yield 'apostrophe' => ["D'Amico", "D'Amico"];
        yield 'right single quotation mark' => ["D\u{2019}Amico", "D'Amico"];
        yield 'French' => ['Lefèvre-Çelik', 'Lefèvre-Çelik'];
        yield 'Italian' => ['Morosoli Bernasconi', 'Morosoli Bernasconi'];
        yield 'Romansh' => ['Caflisch', 'Caflisch'];
        yield 'first letter of the ISO 8859-1 range' => ['Àlvarez', 'Àlvarez'];
        yield 'last letter of the ISO 8859-1 range' => ['Ÿves', 'Ÿves'];
        yield 'letters around the multiplication sign' => ['ÖØ', 'ÖØ'];
        yield 'letters around the division sign' => ['öø', 'öø'];
        yield 'small y with diaeresis' => ['Lÿs', 'Lÿs'];
        yield 'first letter of Latin Extended-A' => ['Ābele', 'Ābele'];
        yield 'Latin Extended-A' => ['Kovačević', 'Kovačević'];
        yield 'Latin Extended-A, last letters' => ['Žižek', 'Žižek'];
        yield 'dotless i' => ['Kılıç', 'Kılıç'];
        yield 'dotted capital I' => ['İnce', 'İnce'];
        yield 'Polish' => ['Łukaszewicz', 'Łukaszewicz'];
    }

    #[DataProvider('validFamilyNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
    }

    public function testGivenNameMayHoldSeveralWords(): void
    {
        self::assertSame('Anna Maria', GivenName::from('Anna  Maria')->value);
        self::assertSame('Jean-Luc', GivenName::from('Jean-Luc')->value);
        self::assertSame('Zoë', GivenName::from('Zoë')->value);
        self::assertSame('Gian Duri', GivenName::from('Gian Duri')->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidFamilyNames(): iterable
    {
        $invalid = 'Invalid Swiss family name (invalid character).';

        yield 's with comma below, Latin Extended-B' => ['Ștefan', $invalid];
        yield 't with comma below, Latin Extended-B' => ['Țurcan', $invalid];
        yield 'o with horn, Latin Extended-B' => ['Ơn', $invalid];
        yield 'multiplication sign' => ['A×B', $invalid];
        yield 'division sign' => ['A÷B', $invalid];
        yield 'Cyrillic' => ['Мюллер', $invalid];
        yield 'Greek' => ['Μύλλερ', $invalid];
        yield 'digit' => ['Müller2', $invalid];
        yield 'asterisks of a missing name' => ['***', $invalid];
        yield 'two hyphens' => ['Müller--Brunner', $invalid];
        yield 'trailing hyphen' => ['Müller-', $invalid];
        $compatibility = 'Invalid Swiss family name (compatibility character).';

        yield 'fullwidth' => ["\u{FF2D}uller", $compatibility];
        yield 'long s of Latin Extended-A' => ["Wei\u{017F}s", $compatibility];
        yield 'n preceded by apostrophe of Latin Extended-A' => ["\u{0149}t", $compatibility];
        yield 'feminine ordinal indicator' => ["\u{00AA}na", $compatibility];
        yield 'micro sign' => ['Mµller', $compatibility];
        yield 'empty' => ['  ', 'Invalid Swiss family name (0 characters).'];
    }

    #[DataProvider('invalidFamilyNames')]
    public function testFamilyNameRefusesCharactersOutsideTheRegisterSet(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): FamilyName => FamilyName::from($input),
            $input,
            $message,
        );
    }

    public function testGivenNameRefusesOtherScripts(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): GivenName => GivenName::from('Δημήτρης'),
            'Δημήτρης',
            'Invalid Swiss given name (invalid character).',
        );
    }
}
