<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Exception;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;

final class AmbiguousJurisdictionException extends InvalidValueObjectException
{
    /**
     * @param non-empty-string $document
     * @param non-empty-list<Subdivision> $jurisdictions
     */
    public function __construct(string $document, private readonly array $jurisdictions)
    {
        parent::__construct(\sprintf(
            '%s number fits several jurisdictions: %s.',
            $document,
            \implode(', ', \array_map(static fn (Subdivision $subdivision): string => $subdivision->isoCode(), $jurisdictions)),
        ));
    }

    /**
     * @return non-empty-list<Subdivision>
     */
    public function jurisdictions(): array
    {
        return $this->jurisdictions;
    }
}
