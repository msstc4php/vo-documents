<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use Normalizer;

/**
 * The passport spelling of a reading by MOFA's ヘボン式ローマ字綴方表 and its 撥音, 促音 and 長音 rules: `ん` is M before
 * B, M and P; `っ` doubles the next consonant, T before CH; `ー` is omitted, `う` after O or U and `お` after O are
 * omitted, except a final `お` (SENOO). The iteration marks `ゝゞヽヾ` repeat the kana before them. Katakana is folded
 * to hiragana for the derivation only. A reading that starts with a small kana, `ん` or `っ`, or has a kana outside
 * the table, has no spelling. `っ` before Y or W (YYA, WWA) follows the general "子音を重ねて" rule; MOFA gives no
 * example. Kana cannot tell a long vowel from a morpheme boundary: a non-long-vowel おう/うう/おお across a morpheme
 * boundary, e.g. 井上 INOUE, 大内 OUCHI, is derived as a long vowel (INOE, OCHI) and needs the Latin override. See
 * docs/sources/JP.md §3.
 *
 * @see https://www.ezairyu.mofa.go.jp/passport/hebon.html ヘボン式ローマ字綴方表 (外務省)
 *
 * @internal
 */
final readonly class PassportHepburn
{
    private const string NO_FORM = 'Invalid Japanese name reading (no Latin form).';

    private const string LONG_VOWEL_MARK = "\u{30FC}";

    private const string MORAIC_N = 'ん';

    private const string SOKUON = 'っ';

    /** Iteration mark => the sound mark the repeated kana takes. */
    private const array ITERATION_MARKS = ['ゝ' => '', 'ゞ' => "\u{3099}"];

    /** The combining voiced and semi-voiced sound marks, NFD of `が` and `ぱ`. */
    private const array SOUND_MARKS = ["\u{3099}", "\u{309A}"];

    private const string VOWELS = 'AIUEO';

    private const array BILABIALS = ['B', 'M', 'P'];

    /**
     * The table rows and its "※参考" rows; the ゔ rows take the kana-by-kana spelling (BUA, not BA).
     */
    private const array SYLLABLES = [
        'あ' => 'A', 'い' => 'I', 'う' => 'U', 'え' => 'E', 'お' => 'O',
        'か' => 'KA', 'き' => 'KI', 'く' => 'KU', 'け' => 'KE', 'こ' => 'KO',
        'さ' => 'SA', 'し' => 'SHI', 'す' => 'SU', 'せ' => 'SE', 'そ' => 'SO',
        'た' => 'TA', 'ち' => 'CHI', 'つ' => 'TSU', 'て' => 'TE', 'と' => 'TO',
        'な' => 'NA', 'に' => 'NI', 'ぬ' => 'NU', 'ね' => 'NE', 'の' => 'NO',
        'は' => 'HA', 'ひ' => 'HI', 'ふ' => 'FU', 'へ' => 'HE', 'ほ' => 'HO',
        'ま' => 'MA', 'み' => 'MI', 'む' => 'MU', 'め' => 'ME', 'も' => 'MO',
        'や' => 'YA', 'ゆ' => 'YU', 'よ' => 'YO',
        'ら' => 'RA', 'り' => 'RI', 'る' => 'RU', 'れ' => 'RE', 'ろ' => 'RO',
        'わ' => 'WA', 'ゐ' => 'I', 'ゑ' => 'E', 'を' => 'O',
        'が' => 'GA', 'ぎ' => 'GI', 'ぐ' => 'GU', 'げ' => 'GE', 'ご' => 'GO',
        'ざ' => 'ZA', 'じ' => 'JI', 'ず' => 'ZU', 'ぜ' => 'ZE', 'ぞ' => 'ZO',
        'だ' => 'DA', 'ぢ' => 'JI', 'づ' => 'ZU', 'で' => 'DE', 'ど' => 'DO',
        'ば' => 'BA', 'び' => 'BI', 'ぶ' => 'BU', 'べ' => 'BE', 'ぼ' => 'BO',
        'ぱ' => 'PA', 'ぴ' => 'PI', 'ぷ' => 'PU', 'ぺ' => 'PE', 'ぽ' => 'PO',
        'きゃ' => 'KYA', 'きゅ' => 'KYU', 'きょ' => 'KYO',
        'しゃ' => 'SHA', 'しゅ' => 'SHU', 'しょ' => 'SHO',
        'ちゃ' => 'CHA', 'ちゅ' => 'CHU', 'ちょ' => 'CHO',
        'にゃ' => 'NYA', 'にゅ' => 'NYU', 'にょ' => 'NYO',
        'ひゃ' => 'HYA', 'ひゅ' => 'HYU', 'ひょ' => 'HYO',
        'みゃ' => 'MYA', 'みゅ' => 'MYU', 'みょ' => 'MYO',
        'りゃ' => 'RYA', 'りゅ' => 'RYU', 'りょ' => 'RYO',
        'ぎゃ' => 'GYA', 'ぎゅ' => 'GYU', 'ぎょ' => 'GYO',
        'じゃ' => 'JA', 'じゅ' => 'JU', 'じょ' => 'JO',
        'びゃ' => 'BYA', 'びゅ' => 'BYU', 'びょ' => 'BYO',
        'ぴゃ' => 'PYA', 'ぴゅ' => 'PYU', 'ぴょ' => 'PYO',
        'しぇ' => 'SHIE', 'ちぇ' => 'CHIE', 'てぃ' => 'TEI', 'にぃ' => 'NII', 'にぇ' => 'NIE',
        'ふぁ' => 'FUA', 'ふぃ' => 'FUI', 'ふぇ' => 'FUE', 'ふぉ' => 'FUO',
        'じぇ' => 'JIE', 'でぃ' => 'DEI', 'でゅ' => 'DEYU',
        'うぃ' => 'UI', 'うぇ' => 'UE', 'うぉ' => 'UO',
        "\u{3094}ぁ" => 'BUA', "\u{3094}ぃ" => 'BUI', "\u{3094}" => 'BU', "\u{3094}ぇ" => 'BUE', "\u{3094}ぉ" => 'BUO',
    ];

    /**
     * @return non-empty-string upper-case Latin letters
     *
     * @throws InvalidPersonNameException when the reading has no spelling
     */
    public function toLatin(AbstractJapaneseReading $reading): string
    {
        $units = $this->units($reading->value);
        $last = \count($units) - 1;
        $latin = self::SYLLABLES[$units[0] ?? ''] ?? throw new InvalidPersonNameException(self::NO_FORM);
        for ($index = 1; $index <= $last; $index++) {
            $latin .= $this->spelling($units[$index], $units[$index + 1] ?? null, $latin, $index === $last);
        }

        return $latin;
    }

    /**
     * @return list<string> the reading in hiragana, cut into table syllables and single kana
     */
    private function units(string $reading): array
    {
        $kana = $this->expandIterationMarks(
            \mb_str_split(\str_replace('ヴ', "\u{3094}", \mb_convert_kana($reading, 'c', 'UTF-8')), 1, 'UTF-8'),
        );
        $units = [];
        for ($index = 0, $count = \count($kana); $index < $count; $index++) {
            $pair = $kana[$index] . ($kana[$index + 1] ?? '');
            if (isset(self::SYLLABLES[$pair])) {
                $units[] = $pair;
                $index++;
            } else {
                $units[] = $kana[$index];
            }
        }

        return $units;
    }

    /**
     * `ゝ` repeats the kana before it without the voicing mark, `ゞ` with it (`いすゞ` is `いすず`); katakana `ヽヾ` are
     * already folded to them.
     *
     * @param list<string> $kana
     *
     * @return list<string>
     */
    private function expandIterationMarks(array $kana): array
    {
        foreach ($kana as $index => $character) {
            if (isset(self::ITERATION_MARKS[$character])) {
                $previous = $this->normalized($kana[$index - 1] ?? '', Normalizer::FORM_D);
                $repeated = \str_replace(self::SOUND_MARKS, '', $previous) . self::ITERATION_MARKS[$character];
                $kana[$index] = $this->normalized($repeated, Normalizer::FORM_C);
            }
        }

        return $kana;
    }

    private function normalized(string $kana, int $form): string
    {
        $normalized = Normalizer::normalize($kana, $form);

        return \is_string($normalized) ? $normalized : $kana;
    }

    /**
     * @param string $written the Latin form of the kana before this one, ending with its last letter
     *
     * @throws InvalidPersonNameException
     */
    private function spelling(string $unit, ?string $next, string $written, bool $final): string
    {
        $nextLatin = self::SYLLABLES[$next ?? ''] ?? '';

        return match (true) {
            $unit === self::LONG_VOWEL_MARK => '',
            $unit === self::MORAIC_N => \in_array($nextLatin[0] ?? '', self::BILABIALS, true) ? 'M' : 'N',
            $unit === self::SOKUON => $this->geminate($nextLatin),
            $unit === 'う' && \in_array(\substr($written, -1), ['O', 'U'], true) => '',
            $unit === 'お' && !$final && \str_ends_with($written, 'O') => '',
            default => self::SYLLABLES[$unit] ?? throw new InvalidPersonNameException(self::NO_FORM),
        };
    }

    /**
     * @throws InvalidPersonNameException when no consonant follows
     */
    private function geminate(string $nextLatin): string
    {
        if (\str_starts_with($nextLatin, 'CH')) {
            return 'T';
        }

        if ($nextLatin === '' || \str_contains(self::VOWELS, $nextLatin[0])) {
            throw new InvalidPersonNameException(self::NO_FORM);
        }

        return $nextLatin[0];
    }
}
