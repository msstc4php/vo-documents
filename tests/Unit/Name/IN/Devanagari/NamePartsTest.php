<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\IN\Devanagari;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\AbstractDevanagariNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari\Surname;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "Names": `\p{sc=Devanagari}` words; U+200C and U+200D only inside a word.
 */
#[CoversClass(AbstractDevanagariNamePart::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(Surname::class)]
#[UsesClass(AbstractNamePart::class)]
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
    public static function validNames(): iterable
    {
        yield 'one word' => ['राजेश', 'राजेश'];
        yield 'two words, spaces collapsed' => ['  पीयूष   कुमार ', 'पीयूष कुमार'];
        yield 'conjunct with virama' => [
            "\u{0915}\u{094D}\u{0937}\u{093F}\u{0924}\u{093F}\u{091C}",
            "\u{0915}\u{094D}\u{0937}\u{093F}\u{0924}\u{093F}\u{091C}",
        ];
        yield 'nukta spelt as base and sign' => [
            "\u{0915}\u{093C}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}",
            "\u{0915}\u{093C}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}",
        ];
        yield 'precomposed nukta letter is decomposed by NFC' => [
            "\u{0958}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}",
            "\u{0915}\u{093C}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}",
        ];
        yield 'zero-width joiner after virama' => [
            "\u{0915}\u{094D}\u{200D}\u{0937}",
            "\u{0915}\u{094D}\u{200D}\u{0937}",
        ];
        yield 'zero-width non-joiner after virama' => [
            "\u{0915}\u{094D}\u{200C}\u{0937}",
            "\u{0915}\u{094D}\u{200C}\u{0937}",
        ];
        yield 'hyphen' => ['दास-गुप्ता', 'दास-गुप्ता'];
        yield 'NBSP between words' => ["राय\u{00A0}चौधरी", 'राय चौधरी'];
        yield 'anusvara, visarga, chandrabindu' => [
            "\u{0939}\u{0902}\u{0938}\u{0903}\u{0901}",
            "\u{0939}\u{0902}\u{0938}\u{0903}\u{0901}",
        ];
    }

    #[DataProvider('validNames')]
    public function testNamesAreNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, GivenName::from($input)->value);
        self::assertSame($expected, Surname::from($input)->value);
    }

    public function testBothSpellingsOfANuktaLetterStoreOneValue(): void
    {
        self::assertTrue(
            GivenName::from("\u{0958}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}")
                ->equals(GivenName::from("\u{0915}\u{093C}\u{0941}\u{0930}\u{0948}\u{0936}\u{0940}")),
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNames(): iterable
    {
        yield 'empty' => ['', 'empty'];
        yield 'too long' => [\str_repeat("\u{0905}", 101), 'too long'];
        yield 'Latin' => ['Rajesh', 'invalid character'];
        yield 'Latin among Devanagari' => ["राजे\u{0073}", 'invalid character'];
        yield 'Cyrillic' => ['Раджеш', 'invalid character'];
        yield 'Devanagari digit' => ["राजेश\u{0967}", 'invalid character'];
        yield 'Arabic-Indic digit' => ["राजेश\u{0661}", 'invalid character'];
        yield 'ASCII digit' => ['राजेश1', 'invalid character'];
        yield 'danda' => ["राजेश\u{0964}", 'invalid character'];
        yield 'double danda' => ["राजेश\u{0965}", 'invalid character'];
        yield 'abbreviation sign' => ["\u{0930}\u{0970}", 'invalid character'];
        yield 'full stop' => ['राजेश.', 'invalid character'];
        yield 'zero-width joiner first' => ["\u{200D}राजेश", 'invalid character'];
        yield 'zero-width joiner last' => ["राजेश\u{200D}", 'invalid character'];
        yield 'zero-width non-joiner next to a space' => ["राज\u{200C} ेश", 'invalid character'];
        yield 'zero-width joiner after a hyphen' => ["राज-\u{200D}ेश", 'invalid character'];
        yield 'two joiners in a row' => ["क\u{200D}\u{200C}ष", 'invalid character'];
        yield 'two joiners after a virama' => ["क\u{094D}\u{200D}\u{200C}ष", 'invalid character'];
        yield 'zero-width non-joiner after a consonant' => ["क\u{200C}मल", 'invalid character'];
        yield 'zero-width joiner after a consonant' => ["क\u{200D}मल", 'invalid character'];
        yield 'zero-width non-joiner after a vowel sign' => ["कि\u{200C}मल", 'invalid character'];
        yield 'zero-width joiner before a virama' => ["क\u{200D}\u{094D}ष", 'invalid character'];
        yield 'zero-width joiner after a virama before a vowel sign' => [
            "क\u{094D}\u{200D}\u{093F}",
            'invalid character',
        ];
        yield 'zero-width non-joiner after a final virama' => ["क\u{094D}\u{200C}", 'invalid character'];
        yield 'zero-width space' => ["राज\u{200B}ेश", 'invalid character'];
        yield 'lone virama' => ["\u{094D}", 'invalid character'];
        yield 'lone nukta' => ["\u{093C}", 'invalid character'];
        yield 'word starting with a vowel sign' => ["\u{0947}\u{0936}", 'invalid character'];
        yield 'second word starting with a vowel sign' => ["राज \u{0947}श", 'invalid character'];
        yield 'leading hyphen' => ['-राजेश', 'invalid character'];
        yield 'apostrophe' => ["राज'ेश", 'invalid character'];
    }

    #[DataProvider('invalidNames')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $reason): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): GivenName => GivenName::from($input),
            $input,
            \sprintf('Invalid Indian Devanagari given name (%s).', $reason),
        );
        self::assertNameRefusedWithoutEcho(
            static fn (): Surname => Surname::from($input),
            $input,
            \sprintf('Invalid Indian Devanagari surname (%s).', $reason),
        );
    }
}
