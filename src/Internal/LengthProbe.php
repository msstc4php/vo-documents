<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use ReflectionMethod;

/**
 * Calls the protected `fitsLength()` hook of a jurisdiction class for its factory's "(fits no jurisdiction)" message,
 * so the hook is not public API of the final jurisdiction classes.
 *
 * @internal
 */
final class LengthProbe
{
    private function __construct()
    {
    }

    /**
     * @param class-string $class a jurisdiction class with a static `fitsLength(string): bool` hook
     */
    public static function fits(string $class, string $number): bool
    {
        return new ReflectionMethod($class, 'fitsLength')->invoke(null, $number) === true;
    }
}
