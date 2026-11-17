<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\SA;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {given: string, father: string|null, grandfather: string|null, family: string|null, latin: string}; "latin" is the
 * `Icao\PersonName` string and required, a missing "father", "grandfather" or "family" reads as null. Other keys are
 * refused.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected a Saudi Arabian person name payload with string "given", string or null '
        . 'or missing "father", "grandfather" and "family", string "latin", and no other keys.';

    private const array KEYS = ['given', 'father', 'grandfather', 'family', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{
     *     given: string,
     *     father: string|null,
     *     grandfather: string|null,
     *     family: string|null,
     *     latin: string,
     * }
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
     * @throws InvalidPersonNameException when a field is not a valid name part or Latin form
     */
    #[Override]
    public function decode(array $payload): PersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $father = PayloadFields::nullableString($payload, 'father', self::SHAPE);
        $grandfather = PayloadFields::nullableString($payload, 'grandfather', self::SHAPE);
        $family = PayloadFields::nullableString($payload, 'family', self::SHAPE);

        return new PersonName(
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            $father === null ? null : FatherName::from($father),
            $grandfather === null ? null : GrandfatherName::from($grandfather),
            $family === null ? null : FamilyName::from($family),
            IcaoPersonName::from(PayloadFields::string($payload, 'latin', self::SHAPE)),
        );
    }
}
