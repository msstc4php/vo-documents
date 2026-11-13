<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use Closure;
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
     * Derives the name's Latin form and applies the policy to the override.
     *
     * @param Closure(): IcaoPersonName $derive throws when the name has no derived form
     *
     * @throws InvalidPersonNameException when there is no derived form and the policy needs one, or the policy refuses
     *                                    the override
     */
    final public function effective(Closure $derive, ?IcaoPersonName $override): LatinForm
    {
        try {
            $derived = $derive();
        } catch (InvalidPersonNameException $reason) {
            return new LatinForm($this->withoutDerivedForm($override, $reason), $override);
        }

        $kept = $this->resolve($override, $derived);

        return new LatinForm($kept ?? $derived, $kept);
    }

    /**
     * The Latin form of a name whose derived form cannot be computed (a letter without an ICAO spelling, a Latin form
     * too long): refused unless the policy accepts the override on its own.
     *
     * @param InvalidPersonNameException $reason why the derived form cannot be computed
     *
     * @throws InvalidPersonNameException $reason
     */
    public function withoutDerivedForm(?IcaoPersonName $override, InvalidPersonNameException $reason): IcaoPersonName
    {
        throw $reason;
    }

    /**
     * @return IcaoPersonName the override in the form to store
     *
     * @throws InvalidPersonNameException when the override is refused
     */
    abstract protected function spelling(IcaoPersonName $override): IcaoPersonName;
}
