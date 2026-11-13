<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Icao;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Every expectation is cross-checked with docs/sources/ICAO.md (Doc 9303 Part 3 §6).
 */
#[CoversClass(Transliterator::class)]
#[CoversClass(LatinMultinational::class)]
#[CoversClass(Cyrillic::class)]
#[UsesClass(UnicodeText::class)]
final class TransliteratorTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string EMPTY = 'Invalid personal name (empty).';

    private const string MIXED = 'Invalid personal name (mixed scripts).';

    private const string UNCOVERED = 'Invalid personal name (no ICAO transliteration for a character).';

    private Transliterator $transliterator;

    #[Override]
    protected function setUp(): void
    {
        $this->transliterator = new Transliterator();
    }

    /**
     * @return iterable<string, array{string, non-empty-string}>
     */
    public static function defaultSpellings(): iterable
    {
        yield 'Щ is SHCH' => ['Щербаков', 'SHCHERBAKOV'];
        // Ъ = IE and Е = E (table 6.B), so the brief's OBIEDKOV would drop a letter.
        yield 'Ъ is IE' => ['Объедков', 'OBIEEDKOV'];
        yield 'Ё is E' => ['Фёдоров', 'FEDOROV'];
        yield 'Х Ц Ч Ж' => ['Хацкевич Жуков', 'KHATSKEVICH ZHUKOV'];
        yield 'Ю Я Й Ы Э' => ['Юрий Яблочкина Ыэ', 'IURII IABLOCHKINA YE'];
        yield 'Serbian Ђ U+0402 is D' => ['Ђорђе', 'DORDE'];
        yield 'Ў U+040E is U' => ['Ўсаў', 'USAU'];
        yield 'Ü defaults to UE' => ['Müller', 'MUELLER'];
        yield 'Ñ defaults to N' => ['Ñúñez', 'NUNEZ'];
        yield 'Ł is L' => ['Ł', 'L'];
        yield 'Ø Æ Þ' => ['Øster Ærø Þór', 'OESTER AEROE THOR'];
        yield 'lower-case ß as ẞ is SS' => ['Straße', 'STRASSE'];
        yield 'Đ U+0110 is D' => ['Đorđević', 'DORDEVIC'];
        yield 'dotless ı U+0131 is I' => ['Işık', 'ISIK'];
        yield 'ASCII is upper-cased' => ['Rimsky-Korsakov', 'RIMSKY-KORSAKOV'];
        yield 'whitespace normalised' => ["  van\u{00A0}der\tBerg ", 'VAN DER BERG'];
        yield 'decomposed input' => ["Андреи\u{0306}", 'ANDREI'];
        yield 'Ễ U+1EC4 outside table 6.A is its base letter' => ['Nguyễn', 'NGUYEN'];
        yield 'Ș U+0218 and Ț U+021A (comma below) are S and T' => ['Ștefan Țurcanu', 'STEFAN TURCANU'];
        yield 'Ǿ U+01FE has the table letter Ø as its base' => ['Ǿ', 'OE'];
    }

    /**
     * @param non-empty-string $expected
     */
    #[DataProvider('defaultSpellings')]
    public function testToLatinUsesTheDefaultSpelling(string $national, string $expected): void
    {
        self::assertSame($expected, $this->transliterator->toLatin($national));
    }

    public function testVariantsListEverySpellingWithTheDefaultFirst(): void
    {
        // Doc 9303 Part 3 §6.A: Ü = "UE or UXX or U".
        self::assertSame(['MUELLER', 'MUXXLLER', 'MULLER'], $this->transliterator->variants('Müller'));
    }

    public function testVariantsCombineEveryCharacterInOrder(): void
    {
        self::assertSame(
            ['NUNEZ', 'NUNXXEZ', 'NXXUNEZ', 'NXXUNXXEZ'],
            $this->transliterator->variants('Ñúñez'),
        );
    }

    public function testVariantsOfAWordWithoutAlternativesIsTheDefaultOnly(): void
    {
        self::assertSame(['SHCHERBAKOV'], $this->transliterator->variants('Щербаков'));
    }

    public function testVariantsOfAAndO(): void
    {
        self::assertSame(['AABERG', 'ABERG'], $this->transliterator->variants('Åberg'));
        self::assertSame(['SOEDER', 'SODER'], $this->transliterator->variants('Söder'));
        self::assertSame(['HAEN', 'HAN'], $this->transliterator->variants('Hän'));
        self::assertSame(['NGUYEN'], $this->transliterator->variants('Nguyễn'));
    }

    /**
     * @return iterable<string, array{string, non-empty-string}>
     */
    public static function refusedInput(): iterable
    {
        yield 'Han script' => ['李', self::UNCOVERED];
        yield 'soft sign is not in table 6.B' => ['Игорь', self::UNCOVERED];
        yield 'soft sign after an apostrophe' => ["д'Артаньян", self::UNCOVERED];
        yield 'Latin a inside Cyrillic' => ["Ив\u{0061}нов", self::MIXED];
        yield 'Cyrillic а inside Latin' => ["Iv\u{0430}nov", self::MIXED];
        yield 'digit' => ['Ivanov2', self::UNCOVERED];
        yield 'modifier letter apostrophe U+02BC' => ["O\u{02BC}Brien", self::UNCOVERED];
        yield 'other punctuation' => ['O.Brien', self::UNCOVERED];
        yield 'Ћ U+040B is not in table 6.B' => ['Ћирић', self::UNCOVERED];
        yield 'Ƒ U+0191 has no base letter' => ['ƒred', self::UNCOVERED];
        yield 'Ʒ U+01B7 (ezh) has no base letter' => ['Ʒ', self::UNCOVERED];
        yield 'empty' => ['', self::EMPTY];
        yield 'blank' => [" \t ", self::EMPTY];
        yield 'separators only' => [' - ', self::EMPTY];
        yield 'apostrophe only' => ["'\u{2019}", self::EMPTY];
    }

    public function testListsOnlyCharactersWithATableRowOfTheirOwn(): void
    {
        self::assertTrue($this->transliterator->lists("\u{0132}"));
        self::assertTrue($this->transliterator->lists("\u{0133}"));
        self::assertTrue($this->transliterator->lists('ж'));
        self::assertFalse($this->transliterator->lists('A'));
        self::assertFalse($this->transliterator->lists('ễ'));
        self::assertFalse($this->transliterator->lists("\u{017F}"));
        self::assertFalse($this->transliterator->lists("\u{FF33}"));
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('refusedInput')]
    public function testUncoveredInputIsRefusedWithoutEchoingIt(string $national, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): string => $this->transliterator->toLatin($national),
            $national,
            $message,
        );
    }

    public function testApostrophesAreOmitted(): void
    {
        // Doc 9303 Part 3 §4.6 and Part 4 §4.2.3.1 d: O’CONNOR -> OCONNOR.
        self::assertSame('OCONNOR', $this->transliterator->toLatin("O'Connor"));
        self::assertSame('OCONNOR', $this->transliterator->toLatin("O\u{2019}Connor"));
        self::assertSame('DARK', $this->transliterator->toLatin("Д'Арк"));
        self::assertSame(['DARTAGNAN'], $this->transliterator->variants("D\u{2019}Artagnan"));
    }

    public function testVariantsAreUniqueWithTheDefaultFirst(): void
    {
        self::assertSame(['AAAA', 'AAA', 'AA'], $this->transliterator->variants('Åå'));
    }

    public function testVariantsRefuseUncoveredInput(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): array => $this->transliterator->variants('李'),
            '李',
            'Invalid personal name (no ICAO transliteration for a character).',
        );
    }

    public function testTooManyVariantsAreRefused(): void
    {
        self::assertNameRefusedWithoutEcho(
            fn (): array => $this->transliterator->variants(\str_repeat('Ü', 8)),
            'Ü',
            'Invalid personal name (too many transliteration variants).',
        );
    }

    public function testVariantsAtTheLimitAreReturned(): void
    {
        $variants = $this->transliterator->variants(\str_repeat('Ä', 12));

        self::assertCount(4096, $variants);
        self::assertSame(\str_repeat('AE', 12), $variants[0]);
        self::assertSame(\str_repeat('A', 12), $variants[4095]);
    }

    public function testVariantsJustAboveTheLimitAreRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);

        $this->transliterator->variants(\str_repeat('Ä', 13));
    }

    public function testToLatinIsNotBoundByTheVariantLimit(): void
    {
        self::assertSame(\str_repeat('UE', 20), $this->transliterator->toLatin(\str_repeat('Ü', 20)));
    }
}
