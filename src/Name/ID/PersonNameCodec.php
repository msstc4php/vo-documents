<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ID;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {name: string, latin: string}, both required; "latin" is the `Icao\PersonName` string. Other keys are refused.
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
     * @return array{name: string, latin: string}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        if (!$value instanceof PersonName) {
            $message = \sprintf('%s encodes %s, %s given.', self::class, PersonName::class, $value::class);

            throw new LogicException($message);
        }

        return $value->jsonSerialize();
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
            IcaoPersonName::from(PayloadFields::string($payload, 'latin', self::SHAPE)),
        );
    }
}
