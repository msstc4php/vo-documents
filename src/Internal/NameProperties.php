<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use ReflectionClass;
use ReflectionProperty;

/**
 * The instance properties of a name class and its parents, private ones of each parent included, built once per
 * class (readonly classes cannot hold the cache themselves).
 *
 * @internal
 */
final class NameProperties
{
    /** @var array<class-string, list<ReflectionProperty>> */
    private static array $byClass = [];

    private function __construct()
    {
    }

    /**
     * @param class-string $class
     *
     * @return list<ReflectionProperty>
     */
    public static function of(string $class): array
    {
        return self::$byClass[$class] ??= self::declared(new ReflectionClass($class));
    }

    /**
     * @param ReflectionClass<object> $reflection
     *
     * @return list<ReflectionProperty>
     */
    private static function declared(ReflectionClass $reflection): array
    {
        $properties = [];
        for (; $reflection !== false; $reflection = $reflection->getParentClass()) {
            foreach ($reflection->getProperties() as $property) {
                if (!$property->isStatic() && $property->getDeclaringClass()->name === $reflection->name) {
                    $properties[] = $property;
                }
            }
        }

        return $properties;
    }
}
