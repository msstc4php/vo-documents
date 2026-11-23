<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ES;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {firstSurname: string, secondSurname: string|null, given: string, latinOverride: string|null}; secondSurname is null
 * for one surname, latinOverride holds only an override, the derived form is never stored; a missing "secondSurname" or
 * "latinOverride" key reads as null. The 0.x keys `first`, `second` and `latin` are read as `firstSurname`,
 * `secondSurname` and `latinOverride`, so the `jsonSerialize()` view (whose `latin` is the full Latin form) reads back
 * as the same name; other keys are refused.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected a Spanish person name payload with string "firstSurname" and "given", '
        . 'string, null or missing "secondSurname" and "latinOverride", and no other keys.';

    private const array LEGACY_KEYS = ['first' => 'firstSurname', 'second' => 'secondSurname']
        + PayloadFields::LEGACY_LATIN_OVERRIDE;

    private const array KEYS = ['firstSurname', 'secondSurname', 'given', 'latinOverride'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{firstSurname: string, secondSurname: string|null, given: string, latinOverride: string|null}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        if (!$value instanceof PersonName) {
            $message = \sprintf('%s encodes %s, %s given.', self::class, PersonName::class, $value::class);

            throw new LogicException($message);
        }

        return [
            'firstSurname' => $value->firstSurname->value,
            'secondSurname' => $value->secondSurname?->value,
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
        $payload = PayloadFields::upgraded($payload, self::LEGACY_KEYS, self::SHAPE);
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $secondSurname = PayloadFields::nullableString($payload, 'secondSurname', self::SHAPE);
        $latin = PayloadFields::nullableString($payload, 'latinOverride', self::SHAPE);

        return new PersonName(
            FamilyName::from(PayloadFields::string($payload, 'firstSurname', self::SHAPE)),
            $secondSurname === null ? null : FamilyName::from($secondSurname),
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
