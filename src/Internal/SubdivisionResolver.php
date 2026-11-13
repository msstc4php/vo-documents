<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use Closure;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;

/**
 * Finds the subdivisions whose format a bare number fits, parsing it once per subdivision, and never picks one of
 * several. The resolution behind every per-country sub-national document factory.
 *
 * @internal
 *
 * @template-covariant TSubdivision of Subdivision
 * @template-covariant TNumber of ValueObject
 */
final readonly class SubdivisionResolver
{
    /**
     * @param non-empty-string $document the document's name in exception messages, e.g. "Australian driver licence"
     * @param list<TSubdivision> $subdivisions in the order matches are reported
     * @param Closure(TSubdivision, string): ?TNumber $tryParse the subdivision's number, null if it does not fit
     * @param Closure(TSubdivision, string): bool $fitsLength whether the number has a length the subdivision allows
     */
    public function __construct(
        private string $document,
        private array $subdivisions,
        private Closure $tryParse,
        private Closure $fitsLength,
    ) {
    }

    /**
     * @return TNumber
     *
     * @throws AmbiguousJurisdictionException<TSubdivision> when several subdivisions match, listed in the given order
     * @throws InvalidDocumentNumberException when none matches: "(fits no jurisdiction)" when the length fits at least
     *                                        one subdivision, otherwise the length
     */
    public function resolve(string $number): ValueObject
    {
        $matches = $this->parseAll($number);

        if (\count($matches) === 1) {
            return $matches[0][1];
        }

        if ($matches === []) {
            throw $this->noMatch($number);
        }

        $subdivisions = \array_map(static fn (array $match): Subdivision => $match[0], $matches);

        throw new AmbiguousJurisdictionException($this->document, $subdivisions);
    }

    /**
     * @return list<TSubdivision> in the given order
     */
    public function matching(string $number): array
    {
        return \array_map(static fn (array $match): Subdivision => $match[0], $this->parseAll($number));
    }

    private function noMatch(string $number): InvalidDocumentNumberException
    {
        $document = $this->document . ' number';
        foreach ($this->subdivisions as $subdivision) {
            if (($this->fitsLength)($subdivision, $number)) {
                return new InvalidDocumentNumberException(\sprintf('Invalid %s (fits no jurisdiction).', $document));
            }
        }

        return Digits::invalidLength(Digits::strip($number), $document);
    }

    /**
     * @return list<array{TSubdivision, TNumber}>
     */
    private function parseAll(string $number): array
    {
        $matches = [];
        foreach ($this->subdivisions as $subdivision) {
            $parsed = ($this->tryParse)($subdivision, $number);
            if ($parsed !== null) {
                $matches[] = [$subdivision, $parsed];
            }
        }

        return $matches;
    }
}
