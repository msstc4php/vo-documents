<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * @internal
 */
final class Mask
{
    /**
     * @param non-empty-string $formatted
     * @param int<0, max> $visible
     *
     * @return non-empty-string
     */
    public static function keepLastDigits(string $formatted, int $visible): string
    {
        $hidden = (int) \preg_match_all('/[0-9]/', $formatted) - $visible;

        $masked = \preg_replace_callback(
            '/[0-9]/',
            static function (array $match) use (&$hidden): string {
                return $hidden-- > 0 ? '*' : $match[0];
            },
            $formatted,
        );

        if ($masked === null || $masked === '') {
            return \str_repeat('*', \strlen($formatted));
        }

        return $masked;
    }

    /**
     * Masks every character of an ASCII value except the last `$visible`; a negative count shows none.
     *
     * @param non-empty-string $value
     *
     * @return non-empty-string
     */
    public static function keepLastCharacters(string $value, int $visible): string
    {
        $shown = \min($visible, \strlen($value));

        return \str_pad(\substr($value, \strlen($value) - $shown), \strlen($value), '*', \STR_PAD_LEFT);
    }
}
