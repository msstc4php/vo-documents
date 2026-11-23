<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\NL;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonNameCodec;
use Override;

/**
 * {family: string, given: string|null, latinOverride: string|null}; given is null for a person with one name,
 * latinOverride holds only an override, the derived form is never stored; a missing "given" or "latinOverride" key
 * reads as null. Prefixes stay inside `family` as typed (`van der Berg`). The 0.x key `latin` is read as
 * `latinOverride`, so the `jsonSerialize()` view (whose `latin` is the full Latin form) reads back as the same name;
 * other keys are refused.
 *
 * @extends AbstractFamilyGivenPersonNameCodec<PersonName, string|null>
 */
final readonly class PersonNameCodec extends AbstractFamilyGivenPersonNameCodec
{
    private const string SHAPE = 'Expected a Dutch person name payload with string "family", '
        . 'string, null or missing "given" and "latinOverride", and no other keys.';

    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    #[Override]
    protected function shape(): string
    {
        return self::SHAPE;
    }

    #[Override]
    protected function create(string $family, ?string $given, ?IcaoPersonName $latin): PersonName
    {
        return new PersonName(FamilyName::from($family), $given === null ? null : GivenName::from($given), $latin);
    }

    #[Override]
    protected function givenValue(AbstractFamilyGivenPersonName $name): ?string
    {
        return $name->given?->value;
    }
}
