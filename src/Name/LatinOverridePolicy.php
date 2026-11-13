<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;

/**
 * Which Latin overrides a country's composite name accepts. Each country picks the subclass its official sources
 * support; an override that is the derived form (hyphen and filler equivalent) is not kept, so equality and the
 * stored payload agree.
 *
 * @internal
 */
abstract readonly class LatinOverridePolicy
{
    /**
     * @return IcaoPersonName|null the override to keep, null when there is none or it is the derived form
     *
     * @throws InvalidPersonNameException when the policy refuses the override
     */
    final public function resolve(?IcaoPersonName $override, IcaoPersonName $derived): ?IcaoPersonName
    {
        // Same MRZ form (a hyphen written as a filler) means the same spelling; checked first, so the derived form is
        // kept even for a name whose spellings are too many to list.
        if (!$override instanceof IcaoPersonName
            || \str_replace('-', '<', $override->value) === \str_replace('-', '<', $derived->value)) {
            return null;
        }

        return $this->spelling($override);
    }

    /**
     * @return IcaoPersonName the override in the form to store
     *
     * @throws InvalidPersonNameException when the override is refused
     */
    abstract protected function spelling(IcaoPersonName $override): IcaoPersonName;
}
