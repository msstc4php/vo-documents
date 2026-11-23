<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use ReflectionClass;
use ReflectionProperty;

trait TamperedValue
{
    /**
     * Builds `$class` holding `$value` without parsing it: the state that reflection, a hydrator or vo-base before 1.3
     * (which restored a serialized `value` unchecked) can produce.
     *
     * @template TValue of AbstractStringValueObject
     *
     * @param class-string<TValue> $class
     *
     * @return TValue
     */
    private static function tampered(string $class, string $value): AbstractStringValueObject
    {
        $object = new ReflectionClass($class)->newInstanceWithoutConstructor();
        new ReflectionProperty($object, 'value')->setValue($object, $value);

        return $object;
    }
}
