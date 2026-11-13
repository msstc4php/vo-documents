<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\AU;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string, latin: string|null}; latin holds only an override, the derived form is never
 * stored. Other keys are refused, so the {@see PersonName::jsonSerialize()} view (which has `effectiveLatin`) is never
 * read as a payload that silently drops an override.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected an Australian person name payload with string "family" and "given", string or null "latin", and no other keys.';

    private const array KEYS = ['family', 'given', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{family: string, given: string, latin: string|null}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        if (!$value instanceof PersonName) {
            throw new LogicException(\sprintf('%s encodes %s, %s given.', self::class, PersonName::class, $value::class));
        }

        return [
            'family' => $value->family->value,
            'given' => $value->given->value,
            'latin' => $value->latinOverride()?->value,
        ];
    }

    #[Override]
    public function decode(array $payload): PersonName
    {
        if (\array_diff(\array_keys($payload), self::KEYS) !== []) {
            throw new InvalidPayloadException(self::SHAPE);
        }

        $family = $payload['family'] ?? null;
        $given = $payload['given'] ?? null;
        $latin = $payload['latin'] ?? null;
        if (!\is_string($family) || !\is_string($given) || ($latin !== null && !\is_string($latin))) {
            throw new InvalidPayloadException(self::SHAPE);
        }

        return new PersonName(
            FamilyName::from($family),
            GivenName::from($given),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
