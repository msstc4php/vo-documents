<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\KR;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractFamilyGivenPersonNameCodec;
use Override;

/**
 * @extends AbstractFamilyGivenPersonNameCodec<PersonName>
 */
final readonly class PersonNameCodec extends AbstractFamilyGivenPersonNameCodec
{
    #[Override]
    public function valueObjectClass(): string
    {
        return PersonName::class;
    }

    #[Override]
    protected function shape(): string
    {
        return 'Expected a Korean person name payload with string "family", string or null "given", '
            . 'string "latin", and no other keys.';
    }

    #[Override]
    protected function create(string $family, ?string $given, IcaoPersonName $latin): PersonName
    {
        return new PersonName(FamilyName::from($family), $given === null ? null : GivenName::from($given), $latin);
    }
}
