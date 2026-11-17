<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\JP;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\JP\AbstractJapaneseReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\FamilyNameReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PassportHepburn;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/JP.md §3: MOFA's ヘボン式ローマ字綴方表 row by row, then its 撥音, 促音 and 長音 rules
 * with MOFA's own examples.
 */
#[CoversClass(PassportHepburn::class)]
#[UsesClass(FamilyNameReading::class)]
#[UsesClass(AbstractJapaneseReading::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
final class PassportHepburnTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    private const string NO_FORM = 'Invalid Japanese name reading (no Latin form).';

    /**
     * Every row of the table except ン, which cannot start a reading (see {@see testMoraicN()}).
     *
     * @return iterable<string, array{non-empty-string, non-empty-string}>
     */
    public static function tableRows(): iterable
    {
        $rows = [
            'ア' => 'A', 'イ' => 'I', 'ウ' => 'U', 'エ' => 'E', 'オ' => 'O',
            'カ' => 'KA', 'キ' => 'KI', 'ク' => 'KU', 'ケ' => 'KE', 'コ' => 'KO',
            'サ' => 'SA', 'シ' => 'SHI', 'ス' => 'SU', 'セ' => 'SE', 'ソ' => 'SO',
            'タ' => 'TA', 'チ' => 'CHI', 'ツ' => 'TSU', 'テ' => 'TE', 'ト' => 'TO',
            'ナ' => 'NA', 'ニ' => 'NI', 'ヌ' => 'NU', 'ネ' => 'NE', 'ノ' => 'NO',
            'ハ' => 'HA', 'ヒ' => 'HI', 'フ' => 'FU', 'ヘ' => 'HE', 'ホ' => 'HO',
            'マ' => 'MA', 'ミ' => 'MI', 'ム' => 'MU', 'メ' => 'ME', 'モ' => 'MO',
            'ヤ' => 'YA', 'ユ' => 'YU', 'ヨ' => 'YO',
            'ラ' => 'RA', 'リ' => 'RI', 'ル' => 'RU', 'レ' => 'RE', 'ロ' => 'RO',
            'ワ' => 'WA', 'ヰ' => 'I', 'ヱ' => 'E', 'ヲ' => 'O',
            'ガ' => 'GA', 'ギ' => 'GI', 'グ' => 'GU', 'ゲ' => 'GE', 'ゴ' => 'GO',
            'ザ' => 'ZA', 'ジ' => 'JI', 'ズ' => 'ZU', 'ゼ' => 'ZE', 'ゾ' => 'ZO',
            'ダ' => 'DA', 'ヂ' => 'JI', 'ヅ' => 'ZU', 'デ' => 'DE', 'ド' => 'DO',
            'バ' => 'BA', 'ビ' => 'BI', 'ブ' => 'BU', 'ベ' => 'BE', 'ボ' => 'BO',
            'パ' => 'PA', 'ピ' => 'PI', 'プ' => 'PU', 'ペ' => 'PE', 'ポ' => 'PO',
            'キャ' => 'KYA', 'キュ' => 'KYU', 'キョ' => 'KYO',
            'シャ' => 'SHA', 'シュ' => 'SHU', 'ショ' => 'SHO',
            'チャ' => 'CHA', 'チュ' => 'CHU', 'チョ' => 'CHO',
            'ニャ' => 'NYA', 'ニュ' => 'NYU', 'ニョ' => 'NYO',
            'ヒャ' => 'HYA', 'ヒュ' => 'HYU', 'ヒョ' => 'HYO',
            'ミャ' => 'MYA', 'ミュ' => 'MYU', 'ミョ' => 'MYO',
            'リャ' => 'RYA', 'リュ' => 'RYU', 'リョ' => 'RYO',
            'ギャ' => 'GYA', 'ギュ' => 'GYU', 'ギョ' => 'GYO',
            'ジャ' => 'JA', 'ジュ' => 'JU', 'ジョ' => 'JO',
            'ビャ' => 'BYA', 'ビュ' => 'BYU', 'ビョ' => 'BYO',
            'ピャ' => 'PYA', 'ピュ' => 'PYU', 'ピョ' => 'PYO',
            // ※参考; the ヴ rows take the kana-by-kana spelling (docs/sources/JP.md §3).
            'シェ' => 'SHIE', 'チェ' => 'CHIE', 'ティ' => 'TEI', 'ニィ' => 'NII', 'ニェ' => 'NIE',
            'ファ' => 'FUA', 'フィ' => 'FUI', 'フェ' => 'FUE', 'フォ' => 'FUO',
            'ジェ' => 'JIE', 'ディ' => 'DEI', 'デュ' => 'DEYU',
            'ウィ' => 'UI', 'ウェ' => 'UE', 'ウォ' => 'UO',
            'ヴァ' => 'BUA', 'ヴィ' => 'BUI', 'ヴ' => 'BU', 'ヴェ' => 'BUE', 'ヴォ' => 'BUO',
        ];

        foreach ($rows as $katakana => $latin) {
            yield $katakana => [$katakana, $latin];
            $hiragana = \str_replace('ヴ', "\u{3094}", \mb_convert_kana($katakana, 'c', 'UTF-8'));
            yield $hiragana . ' (hiragana)' => [$hiragana, $latin];
        }
    }

    /**
     * @param non-empty-string $reading
     * @param non-empty-string $latin
     */
    #[DataProvider('tableRows')]
    public function testTableRow(string $reading, string $latin): void
    {
        self::assertSame($latin, $this->latin($reading));
    }

    /**
     * @return iterable<string, array{non-empty-string, non-empty-string}>
     */
    public static function mofaExamples(): iterable
    {
        yield '難波 ナンバ' => ['ナンバ', 'NAMBA'];
        yield '本間 ホンマ' => ['ホンマ', 'HOMMA'];
        yield '三瓶 サンペイ' => ['サンペイ', 'SAMPEI'];
        yield '服部 ハットリ' => ['ハットリ', 'HATTORI'];
        yield '吉川 キッカワ' => ['キッカワ', 'KIKKAWA'];
        yield '発地 ホッチ' => ['ホッチ', 'HOTCHI'];
        yield '八丁 ハッチョウ' => ['ハッチョウ', 'HATCHO'];
        yield 'ニーナ' => ['ニーナ', 'NINA'];
        yield 'シーナ' => ['シーナ', 'SHINA'];
        yield 'サリー' => ['サリー', 'SARI'];
        yield '新菜 ニイナ' => ['ニイナ', 'NIINA'];
        yield 'しいな' => ['しいな', 'SHIINA'];
        yield 'さりい' => ['さりい', 'SARII'];
        yield '日向 ヒュウガ' => ['ヒュウガ', 'HYUGA'];
        yield '裕貴 ユウキ' => ['ユウキ', 'YUKI'];
        yield '優子 ユウコ' => ['ユウコ', 'YUKO'];
        yield '幸太 コウタ' => ['コウタ', 'KOTA'];
        yield '洋子 ヨウコ' => ['ヨウコ', 'YOKO'];
        yield '亮子 リョウコ' => ['リョウコ', 'RYOKO'];
        yield '大野 オオノ' => ['オオノ', 'ONO'];
        yield '大野 おおの' => ['おおの', 'ONO'];
        yield '大河内 オオコウチ' => ['オオコウチ', 'OKOCHI'];
        yield '大西 オオニシ' => ['オオニシ', 'ONISHI'];
        yield '妹尾 セノオ' => ['セノオ', 'SENOO'];
        yield '高藤 タカトオ' => ['タカトオ', 'TAKATOO'];
        yield '横尾 ヨコオ' => ['ヨコオ', 'YOKOO'];
        yield '伊藤 イトウ' => ['イトウ', 'ITO'];
        yield '高藤 タカトウ' => ['タカトウ', 'TAKATO'];
        yield '御園生 ミソノウ' => ['ミソノウ', 'MISONO'];
        yield 'ヴィヴィアン' => ['ヴィヴィアン', 'BUIBUIAN'];
        yield 'ヴォードレール' => ['ヴォードレール', 'BUODORERU'];
    }

    /**
     * @param non-empty-string $reading
     * @param non-empty-string $latin
     */
    #[DataProvider('mofaExamples')]
    public function testMofaExample(string $reading, string $latin): void
    {
        self::assertSame($latin, $this->latin($reading));
    }

    /**
     * @return iterable<string, array{non-empty-string, non-empty-string}>
     */
    public static function moraicN(): iterable
    {
        yield 'final' => ['ケン', 'KEN'];
        yield 'before a vowel' => ['シンイチ', 'SHINICHI'];
        yield 'before Y' => ['ジンヤ', 'JINYA'];
        yield 'before K' => ['カンコ', 'KANKO'];
        yield 'before N' => ['カンナ', 'KANNA'];
        yield 'before B' => ['トンボ', 'TOMBO'];
        yield 'before M' => ['サンミ', 'SAMMI'];
        yield 'before P' => ['ケンペイ', 'KEMPEI'];
        yield 'before BY' => ['サンビャ', 'SAMBYA'];
        yield 'before PY' => ['サンピョ', 'SAMPYO'];
        yield 'before MY' => ['サンミョ', 'SAMMYO'];
        yield 'before ヴ (BU)' => ['カンヴァ', 'KAMBUA'];
        yield 'hiragana' => ['なんば', 'NAMBA'];
        yield 'before a long mark' => ['ケンー', 'KEN'];
    }

    /**
     * @param non-empty-string $reading
     * @param non-empty-string $latin
     */
    #[DataProvider('moraicN')]
    public function testMoraicN(string $reading, string $latin): void
    {
        self::assertSame($latin, $this->latin($reading));
    }

    /**
     * @return iterable<string, array{non-empty-string, non-empty-string}>
     */
    public static function sokuon(): iterable
    {
        yield 'っち (Review Focus 2)' => ['ハッチ', 'HATCHI'];
        yield 'っちゃ' => ['マッチャ', 'MATCHA'];
        yield 'っちゅ' => ['ハッチュ', 'HATCHU'];
        yield 'っし' => ['イッシキ', 'ISSHIKI'];
        yield 'っしょ' => ['イッショ', 'ISSHO'];
        yield 'っつ' => ['ミッツ', 'MITTSU'];
        yield 'っぺ' => ['イッペイ', 'IPPEI'];
        yield 'っさ' => ['ハッサ', 'HASSA'];
        yield 'っじ' => ['ハッジ', 'HAJJI'];
        yield 'っきょ' => ['ハッキョ', 'HAKKYO'];
        yield 'hiragana' => ['はっとり', 'HATTORI'];
    }

    /**
     * @param non-empty-string $reading
     * @param non-empty-string $latin
     */
    #[DataProvider('sokuon')]
    public function testSokuon(string $reading, string $latin): void
    {
        self::assertSame($latin, $this->latin($reading));
    }

    /**
     * @return iterable<string, array{non-empty-string, non-empty-string}>
     */
    public static function longVowels(): iterable
    {
        yield 'ー in a katakana reading' => ['ジョージ', 'JOJI'];
        yield 'ー twice' => ['ターロー', 'TARO'];
        yield 'ー then う' => ['トーウ', 'TO'];
        yield 'ああ kept' => ['サアヤ', 'SAAYA'];
        yield 'えい kept' => ['ケイコ', 'KEIKO'];
        yield 'ええ kept' => ['ネエ', 'NEE'];
        yield 'うお kept' => ['ウオ', 'UO'];
        yield 'final お after U kept' => ['フウオ', 'FUO'];
        yield 'お not after O kept' => ['アオイ', 'AOI'];
        yield 'う not after O or U kept' => ['アウ', 'AU'];
        yield 'final う after U' => ['ユウ', 'YU'];
        yield 'final う after ょ' => ['キョウ', 'KYO'];
        yield 'お after を' => ['ヲオノ', 'ONO'];
        yield 'う after ん kept' => ['ケンウ', 'KENU'];
        yield 'お after ん kept' => ['ケンオ', 'KENO'];
        yield 'final お after ぉ' => ['フォオ', 'FUOO'];
        yield 'お after ぉ' => ['フォオノ', 'FUONO'];
        yield 'mixed hiragana and katakana' => ['さとウ', 'SATO'];
    }

    /**
     * @param non-empty-string $reading
     * @param non-empty-string $latin
     */
    #[DataProvider('longVowels')]
    public function testLongVowel(string $reading, string $latin): void
    {
        self::assertSame($latin, $this->latin($reading));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function readingsWithoutLatinForm(): iterable
    {
        yield 'starts with ん' => ['ンガ'];
        yield 'starts with ン alone' => ['ン'];
        yield 'starts with っ' => ['ッカ'];
        yield 'starts with a small kana' => ['ャマ'];
        yield 'starts with a small vowel' => ['ァイ'];
        yield 'starts with ー' => ['ーナ'];
        yield 'っ at the end' => ['アッ'];
        yield 'っ before a vowel' => ['アッア'];
        yield 'っ before ん' => ['アッン'];
        yield 'っ before っ' => ['アッッカ'];
        yield 'っ before ー' => ['アッー'];
        yield 'small kana after an unlisted kana' => ['カャ'];
        yield 'small vowel after an unlisted kana' => ['カァ'];
        yield 'small ゎ' => ['アヮ'];
        yield 'small ゕ' => ["ア\u{3095}"];
        yield 'ヷ' => ["ア\u{30F7}"];
        yield 'iteration mark ゝ' => ["ス\u{309D}キ"];
        yield 'iteration mark ヽ' => ["ス\u{30FD}キ"];
        yield 'Ainu small katakana' => ["ア\u{31F0}"];
        yield 'hentaigana' => ["ア\u{1B002}"];
    }

    /**
     * @param non-empty-string $reading
     */
    #[DataProvider('readingsWithoutLatinForm')]
    public function testReadingWithoutLatinFormIsRefusedWithoutEcho(string $reading): void
    {
        self::assertNameRefusedWithoutEcho(fn (): string => $this->latin($reading), $reading, self::NO_FORM);
    }

    /**
     * @param non-empty-string $reading
     */
    private function latin(string $reading): string
    {
        return new PassportHepburn()->toLatin(FamilyNameReading::from($reading));
    }
}
