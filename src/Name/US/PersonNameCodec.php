<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\US;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\PayloadFields;
use MSSTC4PHP\ValueObject\Exception\InvalidPayloadException;
use MSSTC4PHP\ValueObject\Payload\PayloadCodec;
use Override;

/**
 * {family: string, given: string|null, middle: string|null, suffix: string|null, latinOverride: string|null}; suffix is
 * the AAMVA code ({@see NameSuffix}), given is null for a person with one name, latinOverride holds only an override,
 * the derived form is never stored; a missing "given", "middle", "suffix" or "latinOverride" key reads as null. The 0.x
 * key `latin` is read as `latinOverride`, so the `jsonSerialize()` view (whose `latin` is the full Latin form) reads
 * back as the same name; other keys are refused.
 *
 * @implements PayloadCodec<PersonName>
 */
final readonly class PersonNameCodec implements PayloadCodec
{
    private const string SHAPE = 'Expected an American person name payload with string "family", '
        . 'string, null or missing "given", "middle", "suffix" and "latinOverride", and no other keys.';

    private const array KEYS = ['family', 'given', 'middle', 'suffix', 'latinOverride'];

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    /**
     * @return array{family: string, given: string|null, middle: string|null, suffix: string|null, latinOverride: string|null}
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
            'middle' => $value->middle?->value,
            'suffix' => $value->suffix?->value,
            'latinOverride' => $value->latinOverride()?->value,
        ];
    }

    /**
     * @throws InvalidPayloadException when the payload does not have the expected shape
     * @throws InvalidPersonNameException when a field is not a valid name part, suffix or Latin form, or a middle name
     *                                    or suffix comes without a given name
     */
    #[Override]
    public function decode(array $payload): PersonName
    {
        $payload = PayloadFields::upgraded($payload, PayloadFields::LEGACY_LATIN_OVERRIDE, self::SHAPE);
        PayloadFields::assertOnlyKeys($payload, self::KEYS, self::SHAPE);
        $given = PayloadFields::nullableString($payload, 'given', self::SHAPE);
        $middle = PayloadFields::nullableString($payload, 'middle', self::SHAPE);
        $suffix = PayloadFields::nullableString($payload, 'suffix', self::SHAPE);
        $latin = PayloadFields::nullableString($payload, 'latinOverride', self::SHAPE);

        return new PersonName(
            FamilyName::from(PayloadFields::string($payload, 'family', self::SHAPE)),
            $given === null ? null : GivenName::from($given),
            $middle === null ? null : MiddleName::from($middle),
            $suffix === null ? null : NameSuffix::fromCode($suffix),
            $latin === null ? null : IcaoPersonName::from($latin),
        );
    }
}
