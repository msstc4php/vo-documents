<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use MSSTC4PHP\ValueObject\Contract\ValueObject;

/**
 * @internal
 */
final class Optional
{
    /**
     * Whether two optional parts are both absent or equal.
     */
    public static function equal(?ValueObject $one, ?ValueObject $other): bool
    {
        return $one instanceof ValueObject
            ? $other instanceof ValueObject && $one->equals($other)
            : !$other instanceof ValueObject;
    }
}
