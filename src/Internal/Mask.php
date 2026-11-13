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
}
