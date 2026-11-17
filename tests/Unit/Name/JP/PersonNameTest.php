<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\JP;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\AnyIcaoSpelling;
use MSSTC4PHP\ValueObject\Documents\Name\JP\AbstractJapaneseNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\JP\AbstractJapaneseReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\FamilyNameReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\GivenName;
use MSSTC4PHP\ValueObject\Documents\Name\JP\GivenNameReading;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PassportHepburn;
use MSSTC4PHP\ValueObject\Documents\Name\JP\PersonName;
use MSSTC4PHP\ValueObject\Documents\Name\LatinForm;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\RU\AbstractRussianNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\RU\FamilyName as RussianFamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\GivenName as RussianGivenName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\PersonName as RussianPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\RussianTransliteration;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/JP.md §3-§4: the Latin form is the MOFA Hepburn spelling of the readings, and any
 * valid ICAO name is kept as an override (非ヘボン式).
 */
#[CoversClass(PersonName::class)]
#[UsesClass(PassportHepburn::class)]
#[UsesClass(FamilyName::class)]
#[UsesClass(GivenName::class)]
#[UsesClass(FamilyNameReading::class)]
#[UsesClass(GivenNameReading::class)]
#[UsesClass(AbstractJapaneseNamePart::class)]
#[UsesClass(AbstractJapaneseReading::class)]
#[UsesClass(AbstractNamePart::class)]
#[UsesClass(LatinOverridePolicy::class)]
#[UsesClass(AnyIcaoSpelling::class)]
#[UsesClass(LatinForm::class)]
#[UsesClass(IcaoPersonName::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(Optional::class)]
#[UsesClass(Transliterator::class)]
#[UsesClass(LatinMultinational::class)]
#[UsesClass(Cyrillic::class)]
#[UsesClass(RussianPersonName::class)]
#[UsesClass(RussianFamilyName::class)]
#[UsesClass(RussianGivenName::class)]
#[UsesClass(AbstractRussianNamePart::class)]
#[UsesClass(RussianTransliteration::class)]
final class PersonNameTest extends TestCase
{
    use RefusesNameWithoutEchoingInput;

    public function testDerivesTheLatinFormFromTheReadings(): void
    {
        $name = self::japaneseName('佐藤', '太郎', 'サトウ', 'タロウ');

        self::assertSame('SATO<<TARO', $name->latin()->value);
        self::assertNull($name->latinOverride());
        self::assertSame('佐藤 太郎', $name->full());
    }

    /**
     * @return iterable<string, array{string, string, string, string, non-empty-string}>
     */
    public static function derivedNames(): iterable
    {
        yield 'kanji given name with a katakana reading' => ['山田', '花子', 'やまだ', 'ハナコ', 'YAMADA<<HANAKO'];
        yield 'reading with mixed hiragana and katakana' => ['伊藤', '洋子', 'いとウ', 'ヨうコ', 'ITO<<YOKO'];
        yield 'っち (Review Focus 2)' => ['八地', '一', 'ハッチ', 'ハジメ', 'HATCHI<<HAJIME'];
        yield 'ん before B (Review Focus 2)' => ['難波', '健', 'ナンバ', 'ケン', 'NAMBA<<KEN'];
        yield 'ん before M (Review Focus 2)' => ['本間', '純一', 'ホンマ', 'ジュンイチ', 'HOMMA<<JUNICHI'];
        yield 'ん before P (Review Focus 2)' => ['三瓶', '新', 'サンペイ', 'シン', 'SAMPEI<<SHIN'];
        yield 'おおの as MOFA writes it (Review Focus 2)' => ['大野', '翔', 'おおの', 'しょう', 'ONO<<SHO'];
        yield 'ー in a katakana reading (Review Focus 2)' => ['佐藤', 'ジョージ', 'サトー', 'ジョージ', 'SATO<<JOJI'];
        yield 'final お kept' => ['妹尾', '恵', 'セノオ', 'メグミ', 'SENOO<<MEGUMI'];
        yield 'katakana name' => ['スミス', 'マリー', 'スミス', 'マリー', 'SUMISU<<MARI'];
    }

    #[DataProvider('derivedNames')]
    public function testDerivedLatinForm(
        string $family,
        string $given,
        string $familyReading,
        string $givenReading,
        string $latin,
    ): void {
        self::assertSame($latin, self::japaneseName($family, $given, $familyReading, $givenReading)->latin()->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function readingsWithoutLatinForm(): iterable
    {
        yield 'family reading starts with a small kana' => ['ャマダ', 'ハナコ'];
        yield 'family reading starts with ん' => ['ンダ', 'ハナコ'];
        yield 'given reading starts with っ' => ['ヤマダ', 'ッカ'];
    }

    #[DataProvider('readingsWithoutLatinForm')]
    public function testAReadingWithoutHepburnFormNeedsAnOverride(string $familyReading, string $givenReading): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => self::japaneseName('山田', '花子', $familyReading, $givenReading),
            $familyReading . ' ' . $givenReading,
            'Invalid Japanese name reading (no Latin form).',
        );

        $name = self::japaneseName('山田', '花子', $familyReading, $givenReading, IcaoPersonName::from('YAMADA<<HANAKO'));

        self::assertSame('YAMADA<<HANAKO', $name->latin()->value);
        self::assertSame('YAMADA<<HANAKO', $name->latinOverride()?->value);
    }

    public function testALatinFormTooLongNeedsAnOverride(): void
    {
        $reading = \str_repeat('シ', 100);

        self::assertNameRefusedWithoutEcho(
            static fn (): PersonName => self::japaneseName('東', '一', $reading, 'ハジメ'),
            $reading,
            'Invalid ICAO personal name (Latin form too long).',
        );
        self::assertSame(
            'HIGASHI<<HAJIME',
            self::japaneseName('東', '一', $reading, 'ハジメ', IcaoPersonName::from('HIGASHI<<HAJIME'))->latin()->value,
        );
    }

    /**
     * @return iterable<string, array{string, string, string, string, string, non-empty-string}>
     */
    public static function nonHepburnSpellings(): iterable
    {
        yield 'long vowel OH (伊藤 ITOH)' => ['伊藤', '洋子', 'イトウ', 'ヨウコ', 'itoh<<yoko', 'ITOH<<YOKO'];
        yield 'long vowel OH (大野 OHNO)' => ['大野', '翔', 'オオノ', 'ショウ', 'OHNO<<SHO', 'OHNO<<SHO'];
        yield 'ヴ as B (BIBIAN)' => ['スミス', 'ヴィヴィアン', 'スミス', 'ヴィヴィアン', 'SUMISU<<BIBIAN', 'SUMISU<<BIBIAN'];
        yield 'foreign spelling' => ['スミス', 'マリー', 'スミス', 'マリー', 'SMITH<<MARIE', 'SMITH<<MARIE'];
    }

    #[DataProvider('nonHepburnSpellings')]
    public function testAnyValidIcaoNameIsKeptAsTheOverride(
        string $family,
        string $given,
        string $familyReading,
        string $givenReading,
        string $latin,
        string $stored,
    ): void {
        $name = self::japaneseName($family, $given, $familyReading, $givenReading, IcaoPersonName::from($latin));

        self::assertSame($stored, $name->latin()->value);
        self::assertSame($stored, $name->latinOverride()?->value);
    }

    public function testAnOverrideThatIsTheDerivedFormIsNotKept(): void
    {
        $name = self::japaneseName('佐藤', '太郎', 'サトウ', 'タロウ', IcaoPersonName::from('sato << taro'));

        self::assertNull($name->latinOverride());
        self::assertTrue($name->equals(self::japaneseName('佐藤', '太郎', 'サトウ', 'タロウ')));
    }

    public function testEquality(): void
    {
        $name = self::japaneseName('佐藤', '太郎', 'サトウ', 'タロウ');

        self::assertTrue($name->equals(self::japaneseName('佐藤', '太郎', 'サトウ', 'タロウ')));
        self::assertFalse($name->equals(self::japaneseName('左藤', '太郎', 'サトウ', 'タロウ')));
        self::assertFalse($name->equals(self::japaneseName('佐藤', '太朗', 'サトウ', 'タロウ')));
        self::assertFalse($name->equals(self::japaneseName('佐藤', '太郎', 'さとう', 'タロウ')));
        self::assertFalse($name->equals(self::japaneseName('佐藤', '太郎', 'サトウ', 'たろう')));
        self::assertFalse($name->equals(self::japaneseName('佐藤', '太郎', 'サトウ', 'タロウ', IcaoPersonName::from('SATOH<<TARO'))));
        self::assertFalse($name->equals(new RussianPersonName(RussianFamilyName::from('Сато'), RussianGivenName::from('Таро'))));
    }

    public function testJsonViewHasTheEffectiveLatinForm(): void
    {
        self::assertSame(
            [
                'family' => '伊藤',
                'given' => '洋子',
                'familyReading' => 'イトウ',
                'givenReading' => 'ようこ',
                'effectiveLatin' => 'ITOH<<YOKO',
            ],
            self::japaneseName('伊藤', '洋子', 'イトウ', 'ようこ', IcaoPersonName::from('ITOH<<YOKO'))->jsonSerialize(),
        );
    }

    private static function japaneseName(
        string $family,
        string $given,
        string $familyReading,
        string $givenReading,
        ?IcaoPersonName $latin = null,
    ): PersonName {
        return new PersonName(
            FamilyName::from($family),
            GivenName::from($given),
            FamilyNameReading::from($familyReading),
            GivenNameReading::from($givenReading),
            $latin,
        );
    }
}
