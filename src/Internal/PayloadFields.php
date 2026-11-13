<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;

/**
 * Validation shared by the payload codecs of national names: a closed key set and string fields, every failure
 * reported with the codec's one shape message.
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
}
