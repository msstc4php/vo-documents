<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CH;

use Override;

/**
 * One or several given names in register order, separated by spaces (`Anna Maria`, `Jean-Luc`). See
 * {@see AbstractSwissNamePart} and docs/sources/CH.md "Names".
 *
 * @see https://www.fedlex.admin.ch/eli/cc/2010/96/de SR 143.111, Art. 5
 */
final class GivenName extends AbstractSwissNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Swiss given name';
    }
}
