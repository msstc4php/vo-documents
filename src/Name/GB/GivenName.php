<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\GB;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Forenames in order, separated by spaces (`Mary Ann`, `Siân`); same letters and separators as {@see FamilyName}.
 * See docs/sources/GB.md "Names".
 *
 * @see https://www.gov.uk/government/publications/names-names-that-cannot-be-used-in-passports/names-names-that-cannot-be-used-in-passports-accessible
 *      HM Passport Office, Names: names that cannot be used in passports, v19.0, § 2.2
 */
final class GivenName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'British given name';
    }
}
