<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Exception;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;

/**
 * An input error like any other: the bare number is not a valid input for a factory's `fromNumber()`.
 *
 * @template TSubdivision of Subdivision
 */
final class AmbiguousJurisdictionException extends InvalidValueObjectException
{
    /**
     * @param non-empty-string $document
     * @param non-empty-list<TSubdivision> $jurisdictions
     */
    public function __construct(string $document, private readonly array $jurisdictions)
    {
        parent::__construct(\sprintf(
            '%s number fits several jurisdictions: %s.',
            $document,
            \implode(', ', \array_map(
                static fn (Subdivision $subdivision): string => $subdivision->isoCode(),
                $jurisdictions,
            )),
        ));
    }

    /**
     * @return non-empty-list<TSubdivision>
     */
    public function jurisdictions(): array
    {
        return $this->jurisdictions;
    }
}
