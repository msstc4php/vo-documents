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
     * The key of a 0.x payload that held only a Latin override: 1.0 calls it `latinOverride`, so that `latin` means the
     * full Latin form everywhere (views and the required field of the names that never derive it).
     */
    public const array LEGACY_LATIN_OVERRIDE = ['latin' => 'latinOverride'];

    private function __construct()
    {
    }

    /**
     * Moves the fields of a 0.x payload to their 1.0 keys, so stored payloads keep loading.
     *
     * @param array<array-key, mixed> $payload
     * @param non-empty-array<non-empty-string, non-empty-string> $renamed the 0.x key => the 1.0 key
     * @param non-empty-string $shape
     *
     * @return array<array-key, mixed>
     *
     * @throws InvalidPayloadException when the payload holds both keys of a renamed field
     */
    public static function upgraded(array $payload, array $renamed, string $shape): array
    {
        foreach ($renamed as $legacy => $current) {
            if (!\array_key_exists($legacy, $payload)) {
                continue;
            }

            if (\array_key_exists($current, $payload)) {
                throw new InvalidPayloadException($shape);
            }

            $payload[$current] = $payload[$legacy];
            unset($payload[$legacy]);
        }

        return $payload;
    }

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
     * The optional `latinOverride` field of a name that derives its Latin form: only an override is stored.
     *
     * @param array<array-key, mixed> $payload
     * @param non-empty-string $shape
     *
     * @throws InvalidPayloadException when the field is neither missing, null nor a string
     * @throws InvalidPersonNameException when it is not a valid ICAO personal name
     */
    public static function latinOverride(array $payload, string $shape): ?IcaoPersonName
    {
        $latin = self::nullableString($payload, 'latinOverride', $shape);

        return $latin === null ? null : IcaoPersonName::from($latin);
    }
}
