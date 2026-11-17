<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\KR;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * A Korean name part in Hangul: syllables of `\p{sc=Hangul}`, and between words (a name registered as the foreigner
 * used it abroad, `마이클 존슨`, `사라·알리`) one space or the middle dot U+00B7, never leading, trailing or doubled.
 * Hanja names are not covered. A conjoining jamo left over after NFC (it does not complete a syllable) and a
 * compatibility jamo (`ㄱ` U+3131, NFKC differs) are refused: a registered name is written in syllables. See
 * docs/sources/KR.md "Names".
 */
abstract class AbstractKoreanNamePart extends AbstractNamePart
{
    private const string LONE_JAMO = '\x{1100}-\x{11FF}\x{A960}-\x{A97F}\x{D7B0}-\x{D7FF}';

    #[Override]
    protected function letters(): string
    {
        return '\p{sc=Hangul}';
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return " \u{00B7}";
    }

    #[Override]
    protected function word(): string
    {
        return '(?:(?![' . self::LONE_JAMO . '])[' . $this->letters() . '])+';
    }
}
