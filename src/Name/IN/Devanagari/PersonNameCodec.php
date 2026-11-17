<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {given: string, surname: string|null, latin: string}; "latin" is the `Icao\PersonName` string and required, a missing
 * "surname" reads as null. Other keys are refused.
 *
 * @phpstan-import-type InDevanagariPersonNameShape from PersonName
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected an Indian Devanagari person name payload with string "given", '
        . 'string or null or missing "surname", string "latin", and no other keys.';

    private const array KEYS = ['given', 'surname', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return InDevanagariPersonNameShape
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        return PayloadFields::encodable($value, PersonName::class, self::class)->jsonSerialize();
    }

    /**
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name part or Latin form
     */
    #[Override]
    public function decode(array $payload): PersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $surname = PayloadFields::nullableString($payload, 'surname', self::SHAPE);

        return new PersonName(
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            $surname === null ? null : Surname::from($surname),
            PayloadFields::latin($payload, self::SHAPE),
        );
    }
}
