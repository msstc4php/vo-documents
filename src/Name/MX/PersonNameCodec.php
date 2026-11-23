<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\MX;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {first: string|null, second: string|null, given: string, latinOverride: string|null}; second is null for one surname,
 * first and second for none, latinOverride holds only an override, the derived form is never stored; a missing "first",
 * "second" or "latinOverride" key reads as null. The 0.x key `latin` is read as `latinOverride`, so the
 * `jsonSerialize()` view (whose `latin` is the full Latin form) reads back as the same name; other keys are refused.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected a Mexican person name payload with string "given", '
        . 'string, null or missing "first", "second" and "latinOverride", and no other keys.';

    private const array KEYS = ['first', 'second', 'given', 'latinOverride'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{first: string|null, second: string|null, given: string, latinOverride: string|null}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        if (!$value instanceof PersonName) {
            $message = \sprintf('%s encodes %s, %s given.', self::class, PersonName::class, $value::class);

            throw new LogicException($message);
        }

        return [
            'first' => $value->first?->value,
            'second' => $value->second?->value,
            'given' => $value->given->value,
            'latinOverride' => $value->latinOverride()?->value,
        ];
    }

    /**
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name part or Latin form
     */
    #[Override]
    public function decode(array $payload): PersonName
    {
        $payload = PayloadFields::upgraded($payload, PayloadFields::LEGACY_LATIN_OVERRIDE, self::SHAPE);
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $first = PayloadFields::nullableString($payload, 'first', self::SHAPE);
        $second = PayloadFields::nullableString($payload, 'second', self::SHAPE);
        $latin = PayloadFields::nullableString($payload, 'latinOverride', self::SHAPE);

        return new PersonName(
            $first === null ? null : FamilyName::from($first),
            $second === null ? null : FamilyName::from($second),
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
