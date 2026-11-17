<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ID;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {name: string, latin: string}, both required; "latin" is the `Icao\PersonName` string. Other keys are refused.
 *
 * @phpstan-import-type IdPersonNameShape from PersonName
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected an Indonesian person name payload with string "name", string "latin", '
        . 'and no other keys.';

    private const array KEYS = ['name', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return IdPersonNameShape
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        return PayloadFields::encodable($value, PersonName::class, self::class)->jsonSerialize();
    }

    /**
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name or Latin form
     */
    #[Override]
    public function decode(array $payload): PersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);

        return new PersonName(
            FullName::from(PayloadFields::string($payload, 'name', self::SHAPE)),
            PayloadFields::latin($payload, self::SHAPE),
        );
    }
}
