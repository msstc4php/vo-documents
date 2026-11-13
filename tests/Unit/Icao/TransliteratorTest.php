<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Icao;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
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
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function refusedInput(): iterable
    {
        yield 'Han script' => ['李'];
        yield 'soft sign is not in table 6.B' => ['Игорь'];
        yield 'Latin a inside Cyrillic' => ["Ив\u{0061}нов"];
        yield 'Cyrillic а inside Latin' => ["Iv\u{0430}nov"];
        yield 'digit' => ['Ivanov2'];
        yield 'apostrophe' => ["O'Brien"];
        yield 'Ћ U+040B is not in table 6.B' => ['Ћирић'];
        yield 'empty' => [''];
        yield 'blank' => [" \t "];
        yield 'separator only' => ['-'];
    }

    #[DataProvider('refusedInput')]
    public function testUncoveredInputIsRefusedWithoutEchoingIt(string $national): void
    {
        try {
            $this->transliterator->toLatin($national);
            self::fail('Expected exception.');
        } catch (InvalidPersonNameException $e) {
            self::assertContains($e->getMessage(), [
                'Invalid personal name (empty).',
                'Invalid personal name (mixed scripts).',
                'Invalid personal name (no ICAO transliteration for a character).',
            ]);
        }
    }

    public function testVariantsRefuseUncoveredInput(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid personal name (no ICAO transliteration for a character).');

        $this->transliterator->variants('李');
    }

    public function testMixedScriptsAreReportedAsSuch(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid personal name (mixed scripts).');

        $this->transliterator->toLatin("Ив\u{0061}нов");
    }

    public function testEmptyInputIsReportedAsSuch(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid personal name (empty).');

        $this->transliterator->toLatin(' - ');
    }

    public function testTooManyVariantsAreRefused(): void
    {
        $this->expectException(InvalidPersonNameException::class);
        $this->expectExceptionMessage('Invalid personal name (too many transliteration variants).');

        $this->transliterator->variants(\str_repeat('Ü', 8));
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
