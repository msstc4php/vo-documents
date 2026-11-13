<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * @internal
 */
final class Digits
{
    public static function strip(string $input, string $separators = " -\t\u{00A0}"): string
    {
        $characters = \mb_str_split($separators);

        return \str_replace($characters, '', $input);
    }
}
