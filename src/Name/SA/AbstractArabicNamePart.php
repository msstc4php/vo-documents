<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\SA;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * A name part in Arabic: words of `\p{sc=Arabic}` letters joined by a space or a hyphen. The harakat U+064B-0652 and
 * the superscript alef U+0670 (Script=Inherited) are allowed after a letter, never at the start of a word. The
 * tatweel U+0640 (Script=Common) is typographic stretching and is removed, so a stretched and a plain spelling are one
 * value. The Persian kaf U+06A9 and yeh U+06CC, which a Persian keyboard types, are stored as the Arabic kaf U+0643 and
 * yeh U+064A. NFC joins `ا` + U+0654 with `أ` and orders the marks of a letter (fatha before shadda). Only letters
 * (Lo) are name characters: Arabic-Indic digits, Arabic punctuation, Quranic annotation marks and format characters
 * are not. A particle such as `آل` or `بن` is an ordinary word. See docs/sources/SA.md "Names".
 */
abstract class AbstractArabicNamePart extends AbstractNamePart
{
    private const string SCRIPT = '\p{sc=Arabic}';

    private const string LETTER = '(?:(?=\p{Lo})' . self::SCRIPT . ')';

    private const string MARKS = '[\x{064B}-\x{0652}\x{0670}]*';

    private const array FOLDS = ["\u{0640}" => '', "\u{06A9}" => "\u{0643}", "\u{06CC}" => "\u{064A}"];

    #[Override]
    protected function parse(string $value): string
    {
        return parent::parse(\strtr($value, self::FOLDS));
    }

    #[Override]
    protected function letters(): string
    {
        return self::SCRIPT;
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return ' -';
    }

    #[Override]
    protected function word(): string
    {
        return self::LETTER . self::MARKS . '(?:' . self::LETTER . self::MARKS . ')*';
    }
}
