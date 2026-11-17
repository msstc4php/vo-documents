<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN;

use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;

/**
 * The national order of an Indian name in either script: the given name, then the surname when there is one.
 *
 * @internal
 */
final class IndianFullName
{
    /**
     * @return non-empty-string
     */
    public static function of(AbstractNamePart $given, ?AbstractNamePart $surname): string
    {
        return Text::nonEmpty($surname instanceof AbstractNamePart ? $given->value . ' ' . $surname->value : $given->value);
    }
}
