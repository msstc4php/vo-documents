<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CN;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * A Chinese name part: Han characters (`\p{sc=Han}`), and for a minority-nationality name the middle dot U+00B7 between
 * two words (`阿沛·阿旺晋美`), never leading, trailing or doubled. The katakana middle dot U+30FB and the bullet U+2022,
 * which keyboards and systems put in its place, are stored as U+00B7. Uyghur, Tibetan and Mongolian script names are
 * not covered. See docs/sources/CN.md "Names".
 */
abstract class AbstractChineseNamePart extends AbstractNamePart
{
    private const string MIDDLE_DOT = "\u{00B7}";

    private const array DOT_VARIANTS = ["\u{30FB}", "\u{2022}"];

    #[Override]
    protected function letters(): string
    {
        return '\p{sc=Han}';
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return self::MIDDLE_DOT;
    }

    #[Override]
    protected function parse(string $value): string
    {
        return parent::parse(\str_replace(self::DOT_VARIANTS, self::MIDDLE_DOT, $value));
    }
}
