<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;

/**
 * Finds a Latin override among the ICAO spellings of a national name. Hyphens and fillers are equivalent (the MRZ
 * writes RIMSKII-KORSAKOV as RIMSKII<KORSAKOV); the match is returned in the canonical form of the national name.
 *
 * The override is matched word by word, each word among its own variants, so the work is the sum of the variant
 * counts, not their product, and the cap applies to one word.
 *
 * @internal
 */
final class LatinSpelling
{
    /** Bounds the variants of one word for untrusted input, as Icao\Transliterator::variants() does. */
    public const int MAX_SPELLINGS = 4096;

    /**
     * @param IdentifierSpellings|null $secondary null for a name without secondary identifiers
     *
     * @throws InvalidPersonNameException when a word has more than {@see self::MAX_SPELLINGS} variants
     */
    public static function find(
        IcaoPersonName $latin,
        IdentifierSpellings $primary,
        ?IdentifierSpellings $secondary,
    ): ?IcaoPersonName {
        foreach ([...$primary->words, ...$secondary->words ?? []] as $variants) {
            if (\count($variants) > self::MAX_SPELLINGS) {
                throw new InvalidPersonNameException('Invalid personal name (too many transliteration variants).');
            }
        }

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
