<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CA;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Given names in order, separated by spaces (`Marie Ève`, `Jean-François`); same letters and separators as
 * {@see FamilyName}. See docs/sources/CA.md "Names".
 *
 * @see https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-passports/change-name.html IRCC,
 *      "Changing the name on your passport or other travel document"
 */
final class GivenName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Canadian given name';
    }
}
