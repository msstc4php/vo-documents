<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {given: string, surname: string|null, latinOverride: string|null}; latinOverride holds only an override, the derived
 * form is never stored; a missing "surname" or "latinOverride" key reads as null. The 0.x key `latin` is read as
 * `latinOverride`, so the `jsonSerialize()` view (whose `latin` is the full Latin form) reads back as the same name;
 * other keys are refused.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected an Indian person name payload with string "given", '
        . 'string, null or missing "surname" and "latinOverride", and no other keys.';

    private const array KEYS = ['given', 'surname', 'latinOverride'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{given: string, surname: string|null, latinOverride: string|null}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        $value = PayloadFields::encodable($value, PersonName::class, self::class);

        return [
            'given' => $value->given->value,
            'surname' => $value->surname?->value,
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
        $surname = PayloadFields::nullableString($payload, 'surname', self::SHAPE);

        return new PersonName(
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            $surname === null ? null : Surname::from($surname),
            PayloadFields::latinOverride($payload, self::SHAPE),
        );
    }
}
