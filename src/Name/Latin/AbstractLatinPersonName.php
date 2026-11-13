<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\Latin;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\LatinOverridePolicy;
use MSSTC4PHP\ValueObject\Documents\Name\PersonName as NationalPersonName;
use Override;

/**
 * The Latin side of a Latin-script country's composite name: the ICAO form derived from the parts and the override
 * the country's {@see overridePolicy()} keeps. Subclasses hold the parts.
 *
 * @internal
 */
abstract readonly class AbstractLatinPersonName implements NationalPersonName
{
    private IcaoPersonName $effective;

    private ?IcaoPersonName $override;

    /**
     * @param non-empty-list<string> $familyParts the family parts, joined by a space into the primary identifier
     * @param list<string> $givenParts the given parts, one secondary identifier each
     *
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long or the
     *                                    policy refuses $latin
     */
    protected function __construct(array $familyParts, array $givenParts, ?IcaoPersonName $latin)
    {
        $builder = new LatinPersonNameBuilder($familyParts, $givenParts);
        $policy = $this->overridePolicy($builder);
        try {
            $derived = $builder->derive();
        } catch (InvalidPersonNameException $reason) {
            $this->override = $policy->withoutDerivedForm($latin, $reason);
            $this->effective = $this->override;

            return;
        }

        $this->override = $policy->resolve($latin, $derived);
        $this->effective = $this->override ?? $derived;
    }

    #[Override]
    final public function latin(): IcaoPersonName
    {
        return $this->effective;
    }

    /**
     * The Latin form given to the constructor, null when {@see latin()} is derived (also when the given form was the
     * derived one).
     */
    final public function latinOverride(): ?IcaoPersonName
    {
        return $this->override;
    }

    abstract protected function overridePolicy(LatinPersonNameBuilder $builder): LatinOverridePolicy;
}
