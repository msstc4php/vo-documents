<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;

/**
 * Serializes a value object as its value alone and runs `parse()` again on unserialize, so a forged or stale payload never
 * yields an object that `from()` would refuse (vo-base restores `value` without validating it). Any other key of the
 * payload is ignored.
 *
 * @internal
 */
trait ReparsesOnUnserialize
{
    /**
     * @return array{value: string}
     */
    public function __serialize(): array
    {
        return ['value' => $this->value];
    }

    /**
     * @param array<array-key, mixed> $data untrusted: whatever the serialized string holds
     *
     * @throws InvalidValueObjectException when the data holds no string value, or the class's own exception when the
     *                                     value is not valid
     * @throws LogicException when called on an object that already holds a value
     */
    public function __unserialize(array $data): void
    {
        if (isset($this->value)) {
            throw new LogicException('A value object is immutable; __unserialize() runs only on a new object.');
        }

        $value = $data['value'] ?? null;
        if (!\is_string($value)) {
            throw new InvalidValueObjectException('A serialized value object must hold its value, as a string.');
        }

        $this->value = $this->parse($value);
    }
}
