<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ES;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {first: string, second: string|null, given: string, latin: string|null}; second is null for one surname, latin
 * holds only an override, the derived form is never stored. Other keys are refused, so the
 * {@see PersonName::jsonSerialize()} view (which has `effectiveLatin`) is never read as a payload that silently drops
 * an override.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected a Spanish person name payload with string "first" and "given", '
        . 'string or null "second" and "latin", and no other keys.';

    private const array KEYS = ['first', 'second', 'given', 'latin'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{first: string, second: string|null, given: string, latin: string|null}
     */
    #[Override]
    public function encode(ValueObject $value): array
    {
        if (!$value instanceof PersonName) {
            $message = \sprintf('%s encodes %s, %s given.', self::class, PersonName::class, $value::class);

            throw new LogicException($message);
        }

        return [
            'first' => $value->first->value,
            'second' => $value->second?->value,
            'given' => $value->given->value,
            'latin' => $value->latinOverride()?->value,
        ];
    }

    #[Override]
    public function decode(array $payload): PersonName
    {
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $second = PayloadFields::nullableString($payload, 'second', self::SHAPE);
        $latin = PayloadFields::nullableString($payload, 'latin', self::SHAPE);

        return new PersonName(
            FamilyName::from(PayloadFields::string($payload, 'first', self::SHAPE)),
            $second === null ? null : FamilyName::from($second),
            GivenName::from(PayloadFields::string($payload, 'given', self::SHAPE)),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
