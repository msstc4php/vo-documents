<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ES;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * A Spanish name part: words of Latin letters joined by a hyphen, an apostrophe or a space, and the Catalan "ela
 * geminada" `l·l`: the middle dot U+00B7 only between two `l`/`L` (`Marcel·lí`, `Paral·lel`). The ICAO form omits the
 * dot (Doc 9303 Part 3 §4.6, "All other punctuation characters shall be omitted from the MRZ"; {@see PersonName}).
 * `ŀ` (U+0140) and `Ŀ` (U+013F) are stored as their NFKC forms `l·` and `L·`, so both spellings give one value. See
 * docs/sources/ES.md "Names".
 *
 * @internal extended by this package's name parts only, its protected hooks are no API: type-hint the concrete
 *           part or {@see \MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart}
 */
abstract class AbstractSpanishNamePart extends AbstractLatinNamePart
{
    /** Allowed in a part, omitted from the ICAO form. */
    public const array OMITTED_PUNCTUATION = ["\u{00B7}"];

    /**
     * The dot is matched by its code point, never through the letter class: whether a PCRE build counts U+00B7 as
     * Latin depends on its script-extension handling.
     */
    #[Override]
    protected function word(): string
    {
        return '(?:[' . $this->letters() . ']|(?<=[lL])\x{00B7}(?=[lL]))+';
    }

    #[Override]
    protected function parse(string $value): string
    {
        return parent::parse(\str_replace(["\u{0140}", "\u{013F}"], ["l\u{00B7}", "L\u{00B7}"], $value));
    }
}
