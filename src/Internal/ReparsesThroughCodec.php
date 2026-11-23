<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use ReflectionClass;
use TypeError;

/**
 * Serializes a composite name as the payload of the codec that `payloadCodec()` returns and restores it through the
 * codec's `decode()`, so the constructor's checks run again and the derived Latin form is rebuilt from the parts
 * instead of being restored.
 *
 * Payloads written by 0.2 hold PHP's default object layout; they are copied into an unchecked instance, encoded and
 * then decoded the same way. Keys of properties the class does not have are ignored.
 *
 * @internal
 */
trait ReparsesThroughCodec
{
    /**
     * @return array<string, scalar|null>
     */
    public function __serialize(): array
    {
        return static::payloadCodec()->encode($this);
    }

    /**
     * @param array<array-key, mixed> $data untrusted: whatever the serialized string holds
     *
     * @throws InvalidValueObjectException when the payload is malformed
     * @throws InvalidPersonNameException when the parts or the Latin form break the name's rules
     * @throws LogicException when called on an object that already holds a value
     */
    public function __unserialize(array $data): void
    {
        if (\get_object_vars($this) !== []) {
            throw new LogicException('A value object is immutable; __unserialize() runs only on a new object.');
        }

        $codec = static::payloadCodec();
        $restored = $codec->decode(self::isLegacy($data) ? self::legacyPayload($codec, $data) : $data);

        foreach (NameProperties::of(static::class) as $property) {
            $property->setValue($this, $property->getValue($restored));
        }
    }

    /**
     * @return PayloadCodec<static>
     */
    abstract protected static function payloadCodec(): PayloadCodec;

    /**
     * A codec payload holds scalars only; the 0.2 layout holds the name parts as objects.
     *
     * @param array<array-key, mixed> $data
     */
    private static function isLegacy(array $data): bool
    {
        return array_any($data, static fn (mixed $value): bool => \is_object($value));
    }

    /**
     * @param PayloadCodec<ValueObject> $codec
     * @param array<array-key, mixed> $data PHP's default layout: public properties by name, private ones as
     *                                      "\0Class\0name" (no name class has a protected property)
     *
     * @return array<string, scalar|null>
     *
     * @throws InvalidValueObjectException when a property is missing or of the wrong type
     */
    private static function legacyPayload(PayloadCodec $codec, array $data): array
    {
        $object = new ReflectionClass(static::class)->newInstanceWithoutConstructor();

        foreach (NameProperties::of(static::class) as $property) {
            $key = $property->isPrivate()
                ? "\0" . $property->getDeclaringClass()->name . "\0" . $property->name
                : $property->name;
            if (!\array_key_exists($key, $data)) {
                throw self::malformed();
            }

            try {
                $property->setValue($object, $data[$key]);
            } catch (TypeError) {
                throw self::malformed();
            }
        }

        return $codec->encode($object);
    }

    private static function malformed(): InvalidValueObjectException
    {
        return new InvalidValueObjectException(\sprintf('A serialized %s is malformed.', static::class));
    }
}
