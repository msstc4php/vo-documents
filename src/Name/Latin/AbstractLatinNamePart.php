<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\Latin;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * A name part of a Latin-script country: words of Latin letters (diacritics included) joined by a hyphen, an
 * apostrophe or a space. A country that sets a stricter character rule overrides {@see letters()} or
 * {@see innerSeparators()}. Subclasses implement {@see label()}.
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
abstract class AbstractLatinNamePart extends AbstractNamePart
{
    #[Override]
    protected function letters(): string
    {
        return '\p{sc=Latin}';
    }

    /**
     * A word that may carry abbreviation full stops: `Ma.`, `St.Clair`, `M.A.`. A subclass returns it from
     * {@see word()} where the country's records keep abbreviations and initials.
     *
     * @return non-empty-string
     */
    protected function wordWithAbbreviationDots(): string
    {
        return '[' . $this->letters() . ']+(?:\.[' . $this->letters() . ']+)*\.?';
    }
}
