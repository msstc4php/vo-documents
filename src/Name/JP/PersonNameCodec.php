<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\JP;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string, familyReading: string, givenReading: string, latin: string|null}; latin holds only
 * an override, the derived form is never stored; a missing "latin" key reads as null. Other keys are refused, so the
 * {@see PersonName::jsonSerialize()} view (which has `effectiveLatin`) is never read as a payload that silently drops
 * an override.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected a Japanese person name payload with string "family", "given", '
        . '"familyReading" and "givenReading", string, null or missing "latin", and no other keys.';

    private const array KEYS = ['family', 'given', 'familyReading', 'givenReading', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{family: string, given: string, familyReading: string, givenReading: string, latin: string|null}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        $value = PayloadFields::encodable($value, PersonName::class, self::class);

        return [
            'family' => $value->family->value,
            'given' => $value->given->value,
            'familyReading' => $value->familyReading->value,
            'givenReading' => $value->givenReading->value,
            'latin' => $value->latinOverride()?->value,
        ];
    }

    /**
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name part, reading or Latin form
     */
    #[Override]
    public function decode(array $payload): PersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);

        return new PersonName(
            FamilyName::from(PayloadFields::string($payload, 'family', self::SHAPE)),
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            FamilyNameReading::from(PayloadFields::string($payload, 'familyReading', self::SHAPE)),
            GivenNameReading::from(PayloadFields::string($payload, 'givenReading', self::SHAPE)),
            PayloadFields::latinOverride($payload, self::SHAPE),
        );
    }
}
