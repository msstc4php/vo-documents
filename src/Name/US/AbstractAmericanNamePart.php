<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\US;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * A US name part: words of Latin letters (diacritics included) joined by a hyphen, an apostrophe or a space. No federal
 * text lists the letters of a name; the passport crosses diacritics out and writes an apostrophe for the Hawaiian
 * ʻokina, so the ʻokina (U+02BB, or U+2018 as 8 FAM types it) is stored as an apostrophe. See docs/sources/US.md "Names".
 *
 * @see https://fam.state.gov/fam/08fam/08fam040301.html 8 FAM 403.1-3(C)(1) a.(3), 403.1-3(C)(3)
 */
abstract class AbstractAmericanNamePart extends AbstractLatinNamePart
{
    #[Override]
    protected function parse(string $value): string
    {
        return parent::parse(\str_replace(["\u{02BB}", "\u{2018}"], "'", $value));
    }
}
