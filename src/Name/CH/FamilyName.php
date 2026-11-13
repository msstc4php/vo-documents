<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CH;

use Override;

/**
 * The amtlicher Name as the civil status register Infostar holds it, letter case kept (`Müller`, `de Weck`,
 * `Müller-Brunner`). See {@see AbstractSwissNamePart} and docs/sources/CH.md "Names".
 *
 * @see https://www.fedlex.admin.ch/eli/cc/2010/96/de SR 143.111, Art. 4
 */
final class FamilyName extends AbstractSwissNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Swiss family name';
    }
}
