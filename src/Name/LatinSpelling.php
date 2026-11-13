<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;

/**
 * Finds a Latin override among the ICAO spellings of a national name. Hyphens and fillers are equivalent (the MRZ
 * writes RIMSKII-KORSAKOV as RIMSKII<KORSAKOV); the match is returned in the canonical form of the national name.
 *
 * The override is matched word by word, each word among its own variants, so the work is the sum of the variant
 * counts, not their product; the variants of one word are capped where they are listed
 * ({@see \MSSTC4PHP\ValueObject\Documents\Icao\Transliterator::MAX_VARIANTS}).
 *
 * @internal
 */
final class LatinSpelling
{
    /**
     * @param IdentifierSpellings|null $secondary null for a name without secondary identifiers
     */
    public static function find(
        IcaoPersonName $latin,
        IdentifierSpellings $primary,
        ?IdentifierSpellings $secondary,
    ): ?IcaoPersonName {
        $identifiers = \explode('<<', \str_replace('-', '<', $latin->value));
        if (\count($identifiers) !== ($secondary instanceof IdentifierSpellings ? 2 : 1)) {
            return null;
        }

        $primarySpelling = $primary->spell(\explode('<', $identifiers[0]));
        if ($primarySpelling === null || !$secondary instanceof IdentifierSpellings) {
            return $primarySpelling === null ? null : IcaoPersonName::fromParts($primarySpelling);
        }

        $secondarySpelling = $secondary->spell(\explode('<', $identifiers[1] ?? ''));

        return $secondarySpelling === null ? null : IcaoPersonName::fromParts($primarySpelling, [$secondarySpelling]);
    }
}
