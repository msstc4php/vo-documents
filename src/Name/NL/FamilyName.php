<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\NL;

use Override;

/**
 * The geslachtsnaam with its prefixes (voorvoegsels, `van der Berg`, `'t Hart`) as one value, letter case kept: the
 * Paspoortuitvoeringsregeling counts the prefixes as part of the family name. See {@see AbstractDutchNamePart} and
 * docs/sources/NL.md "Names".
 *
 * @see https://wetten.overheid.nl/BWBR0012811/ Paspoortuitvoeringsregeling Nederland 2001, art. 23 lid 3
 */
final class FamilyName extends AbstractDutchNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Dutch family name';
    }
}
