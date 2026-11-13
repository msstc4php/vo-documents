<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\AU;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractFamilyGivenPersonNameCodec;
use Override;

/**
 * {family: string, given: string, latin: string|null}; latin holds only an override, the derived form is never stored;
 * a missing "latin" key reads as null. Other keys are refused, so the {@see PersonName::jsonSerialize()} view (which
 * has `effectiveLatin`) is never read as a payload that silently drops an override.
 *
 * @extends AbstractFamilyGivenPersonNameCodec<PersonName>
 */
final readonly class PersonNameCodec extends AbstractFamilyGivenPersonNameCodec
{
    private const string SHAPE = 'Expected an Australian person name payload with string "family" and "given", '
        . 'string, null or missing "latin", and no other keys.';

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
        return new PersonName(FamilyName::from($family), GivenName::from($given ?? throw $this->malformed()), $latin);
    }
}
