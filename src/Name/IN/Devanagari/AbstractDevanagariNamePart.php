<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * A name part in Devanagari: words of `\p{sc=Devanagari}` (letters, vowel signs, nukta U+093C and virama U+094D are all
 * Script=Devanagari) joined by a space or a hyphen. The Devanagari digits U+0966-096F and the abbreviation sign
 * U+0970 are not name characters (the danda U+0964/0965 is Script=Common and outside the class). The zero-width joiner
 * and non-joiner U+200D, U+200C (Script=Inherited) are allowed, one at a time, only between two letters of a word, where
 * they select a conjunct form. See docs/sources/IN.md "Names".
 */
abstract class AbstractDevanagariNamePart extends AbstractNamePart
{
    private const string LETTER = '(?:(?![\p{Nd}\p{P}])\p{sc=Devanagari})';

    #[Override]
    protected function letters(): string
    {
        return '\p{sc=Devanagari}';
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return ' -';
    }

    #[Override]
    protected function word(): string
    {
        return self::LETTER . '(?:[\x{200C}\x{200D}]?' . self::LETTER . ')*';
    }
}
