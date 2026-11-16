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
     * @param list<non-empty-string> $omittedPunctuation punctuation the country's parts allow and the MRZ omits (Doc
     *                                                   9303 Part 3 §4.6 "All other punctuation characters shall be
     *                                                   omitted from the MRZ"); none by default
     *
     * @throws InvalidPersonNameException when a letter has no ICAO spelling, the Latin form is too long or the
     *                                    policy refuses $latin
     */
    protected function __construct(
        array $familyParts,
        array $givenParts,
        ?IcaoPersonName $latin,
        array $omittedPunctuation = [],
    ) {
        $builder = new LatinPersonNameBuilder($familyParts, $givenParts, $omittedPunctuation);
        $form = $this->overridePolicy($builder)->effective($builder->derive(...), $latin);
        $this->effective = $form->effective;
        $this->override = $form->override;
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

    /**
     * Called from the constructor before the subclass is fully built: build the policy from `$builder` and constants
     * only, never from instance state.
     */
    abstract protected function overridePolicy(LatinPersonNameBuilder $builder): LatinOverridePolicy;
}
