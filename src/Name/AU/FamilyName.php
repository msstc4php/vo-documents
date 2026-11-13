<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\AU;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * Words of Latin letters (diacritics included) joined by a hyphen (`Smith-Jones`), an apostrophe (`O'Brien`) or
 * a space (`van der Berg`). Australia sets no character rule of its own; see docs/sources/AU.md "Names".
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class FamilyName extends AbstractNamePart
{
    #[Override]
    protected function letters(): string
    {
        return '\p{Latin}';
    }

    #[Override]
    protected function label(): string
    {
        return 'Australian family name';
    }
}
