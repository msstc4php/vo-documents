<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Name\CN;

use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CN\AbstractChineseNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\CN\FamilyName;
use MSSTC4PHP\ValueObject\Documents\Name\CN\GivenName;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesNameWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CN.md "Names": Han letters, and a middle dot between words of a minority name.
 */
#[CoversClass(FamilyName::class)]
#[CoversClass(GivenName::class)]
#[CoversClass(AbstractChineseNamePart::class)]
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
        yield 'one character' => ['张', '张'];
        yield 'two characters' => ['欧阳', '欧阳'];
        yield 'traditional' => ['張', '張'];
        yield 'rare character' => ["\u{20000}", "\u{20000}"];
        yield 'iteration mark' => ['々', '々'];
        yield 'minority name, U+00B7' => ["阿沛\u{00B7}阿旺晋美", "阿沛\u{00B7}阿旺晋美"];
        yield 'minority name, katakana middle dot U+30FB' => ["阿沛\u{30FB}阿旺晋美", "阿沛\u{00B7}阿旺晋美"];
        yield 'minority name, bullet U+2022' => ["阿沛\u{2022}阿旺晋美", "阿沛\u{00B7}阿旺晋美"];
        yield 'three words' => ['买买提·吐尔逊·阿里', "买买提\u{00B7}吐尔逊\u{00B7}阿里"];
        yield 'compatibility ideograph is folded by NFC' => ["\u{F900}", "\u{8C48}"];
    }

    #[DataProvider('validNames')]
    public function testFamilyNameIsNormalised(string $input, string $expected): void
    {
        self::assertSame($expected, FamilyName::from($input)->value);
        self::assertSame($expected, GivenName::from($input)->value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNames(): iterable
    {
        $characters = 'Invalid Chinese family name (invalid character).';

        yield 'empty' => ['', 'Invalid Chinese family name (0 characters).'];
        yield 'blank' => ['  ', 'Invalid Chinese family name (0 characters).'];
        yield 'too long' => [\str_repeat('张', 101), 'Invalid Chinese family name (101 characters).'];
        yield 'Latin letters' => ['Zhang', $characters];
        yield 'Latin among Han' => ['张a', $characters];
        yield 'hiragana' => ['さくら', $characters];
        yield 'katakana' => ['サクラ', $characters];
        yield 'hangul' => ['김', $characters];
        yield 'Tibetan script' => ["\u{0F40}\u{0F41}", $characters];
        yield 'Arabic script' => ['علي', $characters];
        yield 'digit' => ['张1', $characters];
        yield 'space' => ['欧 阳', $characters];
        yield 'hyphen' => ['欧-阳', $characters];
        yield 'leading dot' => ["\u{00B7}阿沛", $characters];
        yield 'trailing dot' => ["阿沛\u{00B7}", $characters];
        yield 'doubled dot' => ["阿沛\u{00B7}\u{00B7}阿旺", $characters];
        yield 'dot and bullet in a row' => ["阿沛\u{00B7}\u{2022}阿旺", $characters];
        yield 'lone dot' => ["\u{00B7}", $characters];
        yield 'ASCII full stop for the dot' => ['阿沛.阿旺', $characters];
        yield 'Arabic comma for the dot' => ["阿沛\u{060C}阿旺", $characters];
        yield 'fullwidth Latin' => ["\u{FF21}\u{FF22}", 'Invalid Chinese family name (compatibility character).'];
        yield 'CJK radical (Script=Han, NFKC differs)' => [
            "\u{2F00}",
            'Invalid Chinese family name (compatibility character).',
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
            static fn (): GivenName => GivenName::from('Wei'),
            'Wei',
            'Invalid Chinese given name (invalid character).',
        );
    }

    public function testEveryDotVariantGivesTheSameValue(): void
    {
        $variants = ["\u{00B7}", "\u{30FB}", "\u{2022}"];
        $values = \array_map(
            static fn (string $dot): string => GivenName::from('阿沛' . $dot . '阿旺晋美')->value,
            $variants,
        );

        self::assertSame([$values[0], $values[0], $values[0]], $values);
        self::assertTrue(GivenName::from("阿沛\u{30FB}阿旺晋美")->equals(GivenName::from("阿沛\u{00B7}阿旺晋美")));
    }
}
