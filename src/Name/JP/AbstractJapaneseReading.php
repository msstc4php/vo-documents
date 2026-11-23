<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * The reading (振り仮名) of a Japanese name part: hiragana or katakana, also mixed, and the long-vowel mark `ー` U+30FC
 * (Script=Common; "ー（長音記号）" of 戸籍法施行規則 別表第二), stored as given; the iteration marks `ゝゞヽヾ` are kept as typed
 * and expanded only to derive the Latin form. A reading starts with a kana letter, not with `ー` or an iteration mark.
 * Otherwise it is validated by character set only: a reading that {@see PassportHepburn} cannot spell (one starting
 * with a small kana, `ん` or `っ`) is valid, and its name needs a Latin override. See docs/sources/JP.md §2.
 *
 * @internal extended by this package's name parts only, its protected hooks are no API: type-hint the concrete
 *           part or {@see AbstractNamePart}
 *
 * @see https://laws.e-gov.go.jp/law/322AC0000000224 戸籍法 Art. 13
 */
abstract class AbstractJapaneseReading extends AbstractNamePart
{
    #[Override]
    final protected function letters(): string
    {
        return "\\p{sc=Hiragana}\\p{sc=Katakana}\u{30FC}";
    }

    #[Override]
    final protected function word(): string
    {
        return '(?![\x{30FC}\x{309D}\x{309E}\x{30FD}\x{30FE}])(?:(?=\p{L})[\p{sc=Hiragana}\p{sc=Katakana}]|\x{30FC})+';
    }

    #[Override]
    final protected function innerSeparators(): string
    {
        return '';
    }
}
