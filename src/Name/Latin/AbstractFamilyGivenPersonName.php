<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\Latin;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * A family name and given name(s), the given part optional where the country allows a person with one name. The
 * derived Latin form is FAMILY<<GIVEN, or FAMILY without given names.
 *
 * @internal
 *
 * @template-covariant TFamily of AbstractNamePart the country's family-name part
 * @template-covariant TGiven of AbstractNamePart|null the country's given-name part, nullable where it is optional
 */
abstract readonly class AbstractFamilyGivenPersonName extends AbstractLatinPersonName
{
    /**
     * @param TFamily $family
     * @param TGiven $given
     *
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long or the
     *                                    policy refuses $latin
     */
    protected function __construct(
        public AbstractNamePart $family,
        public ?AbstractNamePart $given,
        ?IcaoPersonName $latin,
    ) {
        parent::__construct([$family->value], $given instanceof AbstractNamePart ? [$given->value] : [], $latin);
    }

    /**
     * @return non-empty-string the given names first, e.g. "Jan Pieter van der Berg"
     */
    #[Override]
    final public function full(): string
    {
        return Text::nonEmpty(
            $this->given instanceof AbstractNamePart
                ? $this->given->value . ' ' . $this->family->value
                : $this->family->value,
        );
    }

    #[Override]
    final public function equals(ValueObject $other): bool
    {
        return $other instanceof static
            && $this->family->equals($other->family)
            && Optional::equal($this->given, $other->given)
            && Optional::equal($this->latinOverride(), $other->latinOverride());
    }

    /**
     * A view, not the storage payload (the country's codec): `effectiveLatin` is {@see latin()}, derived or not.
     *
     * @return array{family: string, given: (TGiven is null ? null : string), effectiveLatin: string}
     */
    #[Override]
    final public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given?->value,
            'effectiveLatin' => $this->latin()->value,
        ];
    }
}
