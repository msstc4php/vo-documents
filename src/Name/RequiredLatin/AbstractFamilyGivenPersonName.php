<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName;
use Override;

/**
 * A family name and a given name in the national script together with the Latin form the passport shows. The Latin
 * form is mandatory and never derived: the passport spelling is a personal choice, and it is not checked against the
 * national form beyond being a valid ICAO personal name. Equality includes the Latin form.
 *
 * @internal
 *
 * @template-covariant TFamily of AbstractNamePart the country's family-name part
 * @template-covariant TGiven of AbstractNamePart the country's given-name part, null for a name stored whole in the
 *                                                 family part
 */
abstract readonly class AbstractFamilyGivenPersonName implements PersonName
{
    /**
     * @param TFamily $family
     * @param TGiven|null $given
     */
    protected function __construct(
        public AbstractNamePart $family,
        public ?AbstractNamePart $given,
        private IcaoPersonName $latin,
    ) {
    }

    #[Override]
    final public function latin(): IcaoPersonName
    {
        return $this->latin;
    }

    #[Override]
    final public function full(): string
    {
        if (!$this->given instanceof AbstractNamePart) {
            return Text::nonEmpty($this->family->value);
        }

        return Text::nonEmpty($this->family->value . $this->nationalSeparator() . $this->given->value);
    }

    #[Override]
    final public function equals(ValueObject $other): bool
    {
        return $other instanceof static
            && $this->family->equals($other->family)
            && Optional::equal($this->given, $other->given)
            && $this->latin->equals($other->latin);
    }

    /**
     * @return array{family: string, given: string|null, latin: string}
     */
    #[Override]
    final public function jsonSerialize(): array
    {
        return [
            'family' => $this->family->value,
            'given' => $this->given?->value,
            'latin' => $this->latin->value,
        ];
    }

    /**
     * What {@see full()} puts between the family and the given name: a space, or nothing where the script is written
     * without one.
     */
    abstract protected function nationalSeparator(): string;
}
