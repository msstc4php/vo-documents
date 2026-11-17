<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\JP;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\JP\AbstractJapaneseNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\JP\AbstractJapaneseReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\FamilyNameReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\GivenNameReading;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/JP.md §1-§2: kanji, kana and ー in a name; kana and ー in a reading; no separator.
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(AbstractJapaneseNamePart::class)]
#[CoversClass(FamilyNameReading::class)]
#[CoversClass(GivenNameReading::class)]
#[CoversClass(AbstractJapaneseReading::class)]
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
        yield 'kanji' => ['佐藤', '佐藤'];
        yield 'iteration mark 々 (Script=Han)' => ['佐々木', '佐々木'];
        yield 'hiragana' => ['さくら', 'さくら'];
        yield 'katakana' => ['サクラ', 'サクラ'];
        yield 'kanji and katakana' => ['ヨシ子', 'ヨシ子'];
        yield 'kanji and hiragana' => ['ゆり子', 'ゆり子'];
        yield 'long-vowel mark ー (Script=Common)' => ['ジョー', 'ジョー'];
        yield 'ヶ' => ['八ヶ代', '八ヶ代'];
        yield 'variant kanji outside the BMP' => ["\u{20BB7}野", "\u{20BB7}野"];
        yield 'old form' => ['髙橋', '髙橋'];
        yield 'combining voiced mark composed by NFC' => ["か\u{3099}", 'が'];
        yield 'combining semi-voiced mark composed by NFC' => ["ハ\u{309A}", 'パ'];
        yield 'trimmed' => [' 佐藤 ', '佐藤'];
    }

    #[DataProvider('validNames')]
    public function testNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
        self::assertSame($expected, GivenName::from($input)->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNames(): iterable
    {
        $characters = 'Invalid Japanese family name (invalid character).';
        $compatibility = 'Invalid Japanese family name (compatibility character).';

        yield 'empty' => ['', 'Invalid Japanese family name (0 characters).'];
        yield 'too long' => [\str_repeat('佐', 101), 'Invalid Japanese family name (101 characters).'];
        yield 'Latin' => ['Sato', $characters];
        yield 'Latin among kanji' => ['佐a', $characters];
        yield 'space' => ['佐 藤', $characters];
        yield 'hyphen' => ['佐-藤', $characters];
        yield 'katakana middle dot U+30FB' => ["マリア\u{30FB}テレサ", $characters];
        yield 'leftover combining mark' => ["ア\u{3099}", $characters];
        yield 'hangul' => ['김', $characters];
        yield 'digit' => ['佐1', $characters];
        yield 'halfwidth katakana' => ['ｻｸﾗ', $compatibility];
        yield 'spacing voiced mark' => ["カ\u{309B}", $compatibility];
        yield 'fullwidth Latin' => ['Ｓａｔｏ', $compatibility];
    }

    #[DataProvider('invalidNames')]
    public function testNameRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(static fn (): FamilyName => FamilyName::from($input), $input, $message);
    }

    public function testGivenNameUsesItsOwnLabel(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): GivenName => GivenName::from('Taro'),
            'Taro',
            'Invalid Japanese given name (invalid character).',
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validReadings(): iterable
    {
        yield 'hiragana' => ['さとう', 'さとう'];
        yield 'katakana' => ['サトウ', 'サトウ'];
        yield 'mixed, stored as given' => ['さとウ', 'さとウ'];
        yield 'long-vowel mark' => ['サトー', 'サトー'];
        yield 'starts with a small kana' => ['ャマ', 'ャマ'];
        yield 'starts with ん' => ['んが', 'んが'];
        yield 'starts with っ' => ['ッカ', 'ッカ'];
        yield 'ヰ' => ['ヰ', 'ヰ'];
        yield 'composed by NFC' => ["さと\u{3099}う", 'さどう'];
    }

    #[DataProvider('validReadings')]
    public function testReadingIsStoredAsGiven(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyNameReading::from($input)->value);
        self::assertSame($expected, GivenNameReading::from($input)->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidReadings(): iterable
    {
        $characters = 'Invalid Japanese family name reading (invalid character).';

        yield 'empty' => ['', 'Invalid Japanese family name reading (0 characters).'];
        yield 'too long' => [\str_repeat('さ', 101), 'Invalid Japanese family name reading (101 characters).'];
        yield 'kanji' => ['佐藤', $characters];
        yield 'iteration mark 々' => ['さ々', $characters];
        yield 'Latin' => ['sato', $characters];
        yield 'space' => ['さ とう', $characters];
        yield 'katakana middle dot' => ["マリア\u{30FB}テレサ", $characters];
        yield 'halfwidth katakana' => ['ｻﾄｳ', 'Invalid Japanese family name reading (compatibility character).'];
    }

    #[DataProvider('invalidReadings')]
    public function testReadingRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): FamilyNameReading => FamilyNameReading::from($input),
            $input,
            $message,
        );
    }

    public function testGivenNameReadingUsesItsOwnLabel(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): GivenNameReading => GivenNameReading::from('太郎'),
            '太郎',
            'Invalid Japanese given name reading (invalid character).',
        );
    }
}
