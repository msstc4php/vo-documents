<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\NL;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string|null, latin: string|null}; given is null for a person with one name, latin holds
 * only an override, the derived form is never stored. Prefixes stay inside `family` as typed (`van der Berg`). Other
 * keys are refused, so the {@see PersonName::jsonSerialize()} view (which has `effectiveLatin`) is never read as a
 * payload that silently drops an override.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected a Dutch person name payload with string "family", '
        . 'string or null "given" and "latin", and no other keys.';

    private const array KEYS = ['family', 'given', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{family: string, given: string|null, latin: string|null}
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
            'given' => $value->given?->value,
            'latin' => $value->latinOverride()?->value,
        ];
    }

    #[Override]
    public function decode(array $payload): PersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $given = PayloadFields::nullableString($payload, 'given', self::SHAPE);
        $latin = PayloadFields::nullableString($payload, 'latin', self::SHAPE);

        return new PersonName(
            FamilyName::from(PayloadFields::string($payload, 'family', self::SHAPE)),
            $given === null ? null : GivenName::from($given),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
