<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;

/**
 * @internal
 */
final class Text
{
    /**
     * Types a stored, already validated value for PHPStan.
     *
     * @return non-empty-string
     */
    public static function nonEmpty(string $value): string
    {
        if ($value === '') {
            throw new LogicException('A stored value is never empty.');
        }

        return $value;
    }
}
