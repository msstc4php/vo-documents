<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;

/**
 * Finds a Latin override among the ICAO spellings of a national name. Hyphens and fillers inside an identifier are
 * equivalent (the MRZ writes RIMSKII-KORSAKOV as RIMSKII<KORSAKOV); the match is returned in the canonical form of
 * the national name.
 *
 * @internal
 */
final class LatinSpelling
{
    /** Bounds the spellings tried for untrusted input, as Icao\Transliterator::variants() does. */
    public const int MAX_SPELLINGS = 4096;

    /**
     * Each accepted form lists the variants of the primary identifier, then of each secondary identifier.
     *
     * @param non-empty-list<non-empty-list<non-empty-list<non-empty-string>>> $forms
     *
     * @throws InvalidPersonNameException when the forms have more than {@see self::MAX_SPELLINGS} spellings
     */
    public static function find(IcaoPersonName $latin, array $forms): ?IcaoPersonName
    {
        $count = 0;
        foreach ($forms as $form) {
            $product = 1;
            foreach ($form as $variants) {
                $product *= \count($variants);
            }

            $count += $product;
            if ($count > self::MAX_SPELLINGS) {
                throw new InvalidPersonNameException('Invalid personal name (too many transliteration variants).');
            }
        }

        $wanted = self::mrzEquivalent($latin->value);
        foreach ($forms as $form) {
            $secondaries = self::combinations(\array_slice($form, 1));
            foreach ($form[0] as $primary) {
                foreach ($secondaries as $secondary) {
                    $candidate = IcaoPersonName::fromParts($primary, $secondary);
                    if (self::mrzEquivalent($candidate->value) === $wanted) {
                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    private static function mrzEquivalent(string $canonical): string
    {
        return \str_replace('-', '<', $canonical);
    }

    /**
     * @param list<non-empty-list<non-empty-string>> $identifiers
     *
     * @return list<list<non-empty-string>>
     */
    private static function combinations(array $identifiers): array
    {
        $combinations = [[]];
        foreach ($identifiers as $variants) {
            $next = [];
            foreach ($combinations as $prefix) {
                foreach ($variants as $variant) {
                    $next[] = [...$prefix, $variant];
                }
            }

            $combinations = $next;
        }

        return $combinations;
    }
}
