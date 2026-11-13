<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao\Table;

use Override;

/**
 * Doc 9303 Part 3 §6.A "Transliteration of Multinational Latin-based Characters", copied from docs/sources/ICAO.md.
 * Unaccented A-Z stand for themselves.
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 *
 * @internal
 */
final readonly class LatinMultinational implements TransliterationTable
{
    private const string UNACCENTED = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @var array<non-empty-string, non-empty-list<non-empty-string>> */
    private const array TABLE = [
        "\u{00C0}" => ['A'],
        "\u{00C1}" => ['A'],
        "\u{00C2}" => ['A'],
        "\u{00C3}" => ['A'],
        "\u{00C4}" => ['AE', 'A'],
        "\u{00C5}" => ['AA', 'A'],
        "\u{00C6}" => ['AE'],
        "\u{00C7}" => ['C'],
        "\u{00C8}" => ['E'],
        "\u{00C9}" => ['E'],
        "\u{00CA}" => ['E'],
        "\u{00CB}" => ['E'],
        "\u{00CC}" => ['I'],
        "\u{00CD}" => ['I'],
        "\u{00CE}" => ['I'],
        "\u{00CF}" => ['I'],
        "\u{00D0}" => ['D'],
        "\u{00D1}" => ['N', 'NXX'],
        "\u{00D2}" => ['O'],
        "\u{00D3}" => ['O'],
        "\u{00D4}" => ['O'],
        "\u{00D5}" => ['O'],
        "\u{00D6}" => ['OE', 'O'],
        "\u{00D8}" => ['OE'],
        "\u{00D9}" => ['U'],
        "\u{00DA}" => ['U'],
        "\u{00DB}" => ['U'],
        "\u{00DC}" => ['UE', 'UXX', 'U'],
        "\u{00DD}" => ['Y'],
        "\u{00DE}" => ['TH'],
        "\u{0100}" => ['A'],
        "\u{0102}" => ['A'],
        "\u{0104}" => ['A'],
        "\u{0106}" => ['C'],
        "\u{0108}" => ['C'],
        "\u{010A}" => ['C'],
        "\u{010C}" => ['C'],
        "\u{010E}" => ['D'],
        "\u{0110}" => ['D'],
        "\u{0112}" => ['E'],
        "\u{0114}" => ['E'],
        "\u{0116}" => ['E'],
        "\u{0118}" => ['E'],
        "\u{011A}" => ['E'],
        "\u{011C}" => ['G'],
        "\u{011E}" => ['G'],
        "\u{0120}" => ['G'],
        "\u{0122}" => ['G'],
        "\u{0124}" => ['H'],
        "\u{0126}" => ['H'],
        "\u{0128}" => ['I'],
        "\u{012A}" => ['I'],
        "\u{012C}" => ['I'],
        "\u{012E}" => ['I'],
        "\u{0130}" => ['I'],
        "\u{0131}" => ['I'],
        "\u{0132}" => ['IJ'],
        "\u{0134}" => ['J'],
        "\u{0136}" => ['K'],
        "\u{0139}" => ['L'],
        "\u{013B}" => ['L'],
        "\u{013D}" => ['L'],
        "\u{013F}" => ['L'],
        "\u{0141}" => ['L'],
        "\u{0143}" => ['N'],
        "\u{0145}" => ['N'],
        "\u{0147}" => ['N'],
        "\u{014A}" => ['N'],
        "\u{014C}" => ['O'],
        "\u{014E}" => ['O'],
        "\u{0150}" => ['O'],
        "\u{0152}" => ['OE'],
        "\u{0154}" => ['R'],
        "\u{0156}" => ['R'],
        "\u{0158}" => ['R'],
        "\u{015A}" => ['S'],
        "\u{015C}" => ['S'],
        "\u{015E}" => ['S'],
        "\u{0160}" => ['S'],
        "\u{0162}" => ['T'],
        "\u{0164}" => ['T'],
        "\u{0166}" => ['T'],
        "\u{0168}" => ['U'],
        "\u{016A}" => ['U'],
        "\u{016C}" => ['U'],
        "\u{016E}" => ['U'],
        "\u{0170}" => ['U'],
        "\u{0172}" => ['U'],
        "\u{0174}" => ['W'],
        "\u{0176}" => ['Y'],
        "\u{0178}" => ['Y'],
        "\u{0179}" => ['Z'],
        "\u{017B}" => ['Z'],
        "\u{017D}" => ['Z'],
        "\u{1E9E}" => ['SS'],
    ];

    #[Override]
    public function script(): string
    {
        return '\p{Latin}';
    }

    #[Override]
    public function spellings(string $character): ?array
    {
        if (\str_contains(self::UNACCENTED, $character)) {
            return [$character];
        }

        return self::TABLE[$character] ?? null;
    }
}
