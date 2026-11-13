<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string, patronymic: string|null, latin: string|null}; latin holds only an override, the
 * derived form is never stored.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
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
            throw new LogicException(\sprintf('%s encodes %s, %s given.', self::class, PersonName::class, $value::class));
        }

        return [
            'family' => $value->family->value,
            'given' => $value->given->value,
            'patronymic' => $value->patronymic?->value,
            'latin' => $value->latinOverride()?->value,
        ];
    }

    #[Override]
    public function decode(array $payload): PersonName
    {
        $family = $payload['family'] ?? null;
        $given = $payload['given'] ?? null;
        $patronymic = $payload['patronymic'] ?? null;
        $latin = $payload['latin'] ?? null;
        if (!\is_string($family) || !\is_string($given)
            || ($patronymic !== null && !\is_string($patronymic)) || ($latin !== null && !\is_string($latin))) {
            throw new InvalidPayloadException('Expected a Russian person name payload with string "family" and "given" and string or null "patronymic" and "latin".');
        }

        return new PersonName(
            FamilyName::from($family),
            GivenName::from($given),
            $patronymic === null ? null : Patronymic::from($patronymic),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
