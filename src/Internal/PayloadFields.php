<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;

/**
 * Validation shared by the payload codecs of national names: the encoded class, a closed key set, string fields and
 * the ICAO `latin` field, every payload failure reported with the codec's one shape message.
 *
 * @internal
 */
final class PayloadFields
{
    /**
     * @param array<array-key, mixed> $payload
     * @param non-empty-list<non-empty-string> $keys
     * @param non-empty-string $shape
     *
     * @throws InvalidPayloadException when the payload has another key
     */
    public static function assertOnlyKeys(array $payload, array $keys, string $shape): void
    {
        if (\array_diff(\array_keys($payload), $keys) !== []) {
            throw new InvalidPayloadException($shape);
        }
    }

    /**
     * @param array<array-key, mixed> $payload
     * @param non-empty-string $key
     * @param non-empty-string $shape
     *
     * @throws InvalidPayloadException when the field is missing or not a string
     */
    public static function string(array $payload, string $key, string $shape): string
    {
        $value = $payload[$key] ?? null;
        if (!\is_string($value)) {
            throw new InvalidPayloadException($shape);
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $payload
     * @param non-empty-string $key
     * @param non-empty-string $shape
     *
     * @throws InvalidPayloadException when the field is neither missing, null nor a string
     */
    public static function nullableString(array $payload, string $key, string $shape): ?string
    {
        $value = $payload[$key] ?? null;
        if ($value !== null && !\is_string($value)) {
            throw new InvalidPayloadException($shape);
        }

        return $value;
    }

    /**
     * @template TValue of ValueObject
     *
     * @param class-string<TValue> $class the class the codec encodes
     * @param class-string $codec
     *
     * @return TValue
     *
     * @throws LogicException when `$value` is of another class
     */
    public static function encodable(ValueObject $value, string $class, string $codec): ValueObject
    {
        if (!$value instanceof $class) {
            throw new LogicException(\sprintf('%s encodes %s, %s given.', $codec, $class, $value::class));
        }

        return $value;
    }

    /**
     * The required `latin` field: the `Icao\PersonName` string of the passport.
     *
     * @param array<array-key, mixed> $payload
     * @param non-empty-string $shape
     *
     * @throws InvalidPayloadException when the field is missing or not a string
     * @throws InvalidPersonNameException when it is not a valid ICAO personal name
     */
    public static function latin(array $payload, string $shape): IcaoPersonName
    {
        return IcaoPersonName::from(self::string($payload, 'latin', $shape));
    }

    /**
     * The optional `latin` field of a name that derives its Latin form: only an override is stored.
     *
     * @param array<array-key, mixed> $payload
     * @param non-empty-string $shape
     *
     * @throws InvalidPayloadException when the field is neither missing, null nor a string
     * @throws InvalidPersonNameException when it is not a valid ICAO personal name
     */
    public static function latinOverride(array $payload, string $shape): ?IcaoPersonName
    {
        $latin = self::nullableString($payload, 'latin', $shape);

        return $latin === null ? null : IcaoPersonName::from($latin);
    }
}
