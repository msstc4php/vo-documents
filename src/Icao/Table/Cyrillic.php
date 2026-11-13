<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao\Table;

use Override;

/**
 * Doc 9303 Part 3 §6.B "Transliteration of Cyrillic Characters", recommended column, copied from
 * docs/sources/ICAO.md. The language-specific "except …" spellings are not alternatives and are left to language
 * tables; characters the table omits (Ь among them) have no entry.
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.B
 *
 * @internal
 */
final readonly class Cyrillic implements TransliterationTable
{
    /** @var array<non-empty-string, non-empty-list<non-empty-string>> */
    private const array TABLE = [
        "\u{0401}" => ['E'],
        "\u{0402}" => ['D'],
        "\u{0404}" => ['IE'],
        "\u{0405}" => ['DZ'],
        "\u{0406}" => ['I'],
        "\u{0407}" => ['I'],
        "\u{0408}" => ['J'],
        "\u{0409}" => ['LJ'],
        "\u{040A}" => ['NJ'],
        "\u{040C}" => ['K'],
        "\u{040E}" => ['U'],
        "\u{040F}" => ['DZ'],
        "\u{0410}" => ['A'],
        "\u{0411}" => ['B'],
        "\u{0412}" => ['V'],
        "\u{0413}" => ['G'],
        "\u{0414}" => ['D'],
        "\u{0415}" => ['E'],
        "\u{0416}" => ['ZH'],
        "\u{0417}" => ['Z'],
        "\u{0418}" => ['I'],
        "\u{0419}" => ['I'],
        "\u{041A}" => ['K'],
        "\u{041B}" => ['L'],
        "\u{041C}" => ['M'],
        "\u{041D}" => ['N'],
        "\u{041E}" => ['O'],
        "\u{041F}" => ['P'],
        "\u{0420}" => ['R'],
        "\u{0421}" => ['S'],
        "\u{0422}" => ['T'],
        "\u{0423}" => ['U'],
        "\u{0424}" => ['F'],
        "\u{0425}" => ['KH'],
        "\u{0426}" => ['TS'],
        "\u{0427}" => ['CH'],
        "\u{0428}" => ['SH'],
        "\u{0429}" => ['SHCH'],
        "\u{042A}" => ['IE'],
        "\u{042B}" => ['Y'],
        "\u{042D}" => ['E'],
        "\u{042E}" => ['IU'],
        "\u{042F}" => ['IA'],
        "\u{046A}" => ['U'],
        "\u{0474}" => ['Y'],
        "\u{0490}" => ['G'],
        "\u{0492}" => ['G'],
        "\u{04BA}" => ['C'],
    ];

    #[Override]
    public function script(): string
    {
        return '\p{Cyrillic}';
    }

    #[Override]
    public function spellings(string $character): ?array
    {
        return self::TABLE[$character] ?? null;
    }
}
