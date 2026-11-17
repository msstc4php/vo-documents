<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * A Japanese name part as the 戸籍 writes it: kanji (`\p{sc=Han}`, which has `々`), hiragana, katakana, the
 * long-vowel mark `ー` U+30FC and `〆` U+3006 (both Script=Common), with no separator. The 常用漢字/人名用漢字 lists bind
 * only a new given name, and surnames such as `〆木` exist, so any Han character and `〆` are accepted. See
 * docs/sources/JP.md §1.
 *
 * @see https://laws.e-gov.go.jp/law/322M40000010094 戸籍法施行規則 Art. 60
 */
abstract class AbstractJapaneseNamePart extends AbstractNamePart
{
    #[Override]
    final protected function letters(): string
    {
        return "\\p{sc=Han}\\p{sc=Hiragana}\\p{sc=Katakana}\u{30FC}\u{3006}";
    }

    #[Override]
    final protected function innerSeparators(): string
    {
        return '';
    }
}
