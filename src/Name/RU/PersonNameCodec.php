<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string, patronymic: string|null, latin: string|null}; latin holds only an override, the
 * derived form is never stored; a missing "patronymic" or "latin" key reads as null. Other keys are refused, so the
 * {@see PersonName::jsonSerialize()} view (which has `effectiveLatin`) is never read as a payload that silently drops
 * an override.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected a Russian person name payload with string "family" and "given", '
        . 'string, null or missing "patronymic" and "latin", and no other keys.';

    private const array KEYS = ['family', 'given', 'patronymic', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{family: string, given: string, patronymic: string|null, latin: string|null}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        if (!$value instanceof PersonName) {
            $message = \sprintf('%s encodes %s, %s given.', self::class, PersonName::class, $value::class);

            throw new LogicException($message);
        }

        return [
            'family' => $value->family->value,
            'given' => $value->given->value,
            'patronymic' => $value->patronymic?->value,
            'latin' => $value->latinOverride()?->value,
        ];
    }

    /**
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name part or Latin form
     */
    #[Override]
    public function decode(array $payload): PersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $patronymic = PayloadFields::nullableString($payload, 'patronymic', self::SHAPE);
        $latin = PayloadFields::nullableString($payload, 'latin', self::SHAPE);

        return new PersonName(
            FamilyName::from(PayloadFields::string($payload, 'family', self::SHAPE)),
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            $patronymic === null ? null : Patronymic::from($patronymic),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
