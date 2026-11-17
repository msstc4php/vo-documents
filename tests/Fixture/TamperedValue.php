<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use PHPUnit\Framework\Assert;

trait TamperedValue
{
    /**
     * Unserializes `$class` holding `$value` without parsing it, as vo-base does for any serialized string.
     *
     * @template TValue of AbstractStringValueObject
     *
     * @param class-string<TValue> $class
     *
     * @return TValue
     */
    private static function tampered(string $class, string $value): AbstractStringValueObject
    {
        $object = \unserialize(
            \sprintf('O:%d:"%s":1:{s:5:"value";s:%d:"%s";}', \strlen($class), $class, \strlen($value), $value),
        );
        Assert::assertInstanceOf($class, $object);

        return $object;
    }
}
