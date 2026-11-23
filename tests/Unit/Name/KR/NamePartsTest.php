<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\KR;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\KR\AbstractKoreanNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\KR\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\KR\GivenName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/KR.md "Names": Hangul only, Hanja not covered, no jamo on their own.
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(AbstractKoreanNamePart::class)]
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
        yield 'one syllable' => ['김', '김'];
        yield 'two syllables' => ['남궁', '남궁'];
        yield 'given name' => ['민준', '민준'];
        yield 'five syllables' => ['가나다라마', '가나다라마'];
        yield 'conjoining jamo are composed by NFC' => ["\u{1100}\u{1161}\u{11A8}", '각'];
        yield 'decomposed 김' => ["\u{1100}\u{1175}\u{11B7}", '김'];
        yield 'foreign name written in Hangul, with a space' => ['마이클  존슨', '마이클 존슨'];
        yield 'foreign name, middle dot' => ["사라\u{00B7}알리", "사라\u{00B7}알리"];
        yield 'foreign name, katakana middle dot U+30FB' => ["사라\u{30FB}알리", "사라\u{00B7}알리"];
        yield 'foreign name, bullet U+2022' => ["사라\u{2022}알리", "사라\u{00B7}알리"];
        yield 'foreign name, hyphen' => ['장-피에르', '장-피에르'];
        yield 'surrounding spaces' => ['  김 ', '김'];
    }

    #[DataProvider('validNames')]
    public function testNamesAreNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
        self::assertSame($expected, GivenName::from($input)->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNames(): iterable
    {
        $characters = 'Invalid Korean family name (invalid character).';

        yield 'empty' => ['', 'Invalid Korean family name (empty).'];
        yield 'too long' => [\str_repeat('김', 101), 'Invalid Korean family name (too long).'];
        yield 'Latin' => ['Kim', $characters];
        yield 'Latin among Hangul' => ['김a', $characters];
        yield 'Hanja' => ['金', $characters];
        yield 'Han among Hangul' => ['김金', $characters];
        yield 'hiragana' => ['さくら', $characters];
        yield 'katakana' => ['サクラ', $characters];
        yield 'Cyrillic' => ['Ким', $characters];
        yield 'digit' => ['김1', $characters];
        yield 'leading hyphen' => ['-김', $characters];
        yield 'trailing hyphen' => ['김-', $characters];
        yield 'doubled hyphen' => ['김--민', $characters];
        yield 'hyphen and space in a row' => ['김- 민', $characters];
        yield 'apostrophe' => ["김'민", $characters];
        yield 'leading dot' => ["\u{00B7}김", $characters];
        yield 'trailing dot' => ["김\u{00B7}", $characters];
        yield 'doubled dot' => ["김\u{00B7}\u{00B7}민", $characters];
        yield 'tone marks only' => ["\u{302E}\u{302E}", $characters];
        yield 'tone mark after a syllable' => ["김\u{302E}", $characters];
        yield 'second tone mark' => ["김\u{302F}", $characters];
        yield 'leading katakana middle dot' => ["\u{30FB}김", $characters];
        yield 'bullet next to a middle dot' => ["김\u{2022}\u{00B7}민", $characters];
        yield 'compatibility jamo ㄱ U+3131' => ["\u{3131}", 'Invalid Korean family name (compatibility character).'];
        yield 'compatibility jamo ㅏ U+314F' => ["김\u{314F}", 'Invalid Korean family name (compatibility character).'];
        yield 'halfwidth Hangul' => ["\u{FFA1}", 'Invalid Korean family name (compatibility character).'];
        yield 'lone leading consonant U+1100' => ["\u{1100}", $characters];
        yield 'lone vowel U+1161' => ["\u{1161}", $characters];
        yield 'lone final consonant U+11A8' => ["김\u{11A8}\u{11A8}", $characters];
        yield 'fullwidth Latin' => [
            "\u{FF2B}\u{FF29}\u{FF2D}",
            'Invalid Korean family name (compatibility character).',
        ];
    }

    #[DataProvider('invalidNames')]
    public function testFamilyNameRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertNameRefusedWithoutEcho(static fn (): FamilyName => FamilyName::from($input), $input, $message);
    }

    public function testGivenNameUsesItsOwnLabel(): void
    {
        self::assertNameRefusedWithoutEcho(
            static fn (): GivenName => GivenName::from('Minjun'),
            'Minjun',
            'Invalid Korean given name (invalid character).',
        );
    }
}
