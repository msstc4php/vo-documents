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
 * The override is split into one group per part (the primary identifier, then as many words per secondary part as
 * that part has) and each group is looked up among its own part's variants, so the work is the sum of the variant
 * counts, not their product.
 *
 * @internal
 */
final class LatinSpelling
{
    /** Bounds the variants of one part for untrusted input, as Icao\Transliterator::variants() does. */
    public const int MAX_SPELLINGS = 4096;

    /**
     * Each accepted form lists the variants of the primary identifier, then of each secondary part; all variants of
     * a part have the same words (only letters differ).
     *
     * @param non-empty-list<non-empty-list<non-empty-list<non-empty-string>>> $forms
     *
     * @throws InvalidPersonNameException when a part has more than {@see self::MAX_SPELLINGS} variants
     */
    public static function find(IcaoPersonName $latin, array $forms): ?IcaoPersonName
    {
        foreach ($forms as $form) {
            foreach ($form as $variants) {
                if (\count($variants) > self::MAX_SPELLINGS) {
                    throw new InvalidPersonNameException('Invalid personal name (too many transliteration variants).');
                }
            }
        }

        $identifiers = \explode('<<', self::mrzEquivalent($latin->value));
        foreach ($forms as $form) {
            $match = self::match($identifiers, $form);
            if ($match !== null) {
                return IcaoPersonName::fromParts($match[0], \array_slice($match, 1));
            }
        }

        return null;
    }

    /**
     * @param non-empty-list<string> $identifiers the override's primary and, if any, secondary identifiers
     * @param non-empty-list<non-empty-list<non-empty-string>> $form
     *
     * @return non-empty-list<non-empty-string>|null the matching variant of each part
     */
    private static function match(array $identifiers, array $form): ?array
    {
        $groups = self::groups($identifiers, $form);
        if ($groups === null) {
            return null;
        }

        $match = [];
        foreach ($form as $index => $variants) {
            $variant = self::variantSpelled($groups[$index], $variants);
            if ($variant === null) {
                return null;
            }

            $match[] = $variant;
        }

        return $match;
    }

    /**
     * @param non-empty-list<string> $identifiers
     * @param non-empty-list<non-empty-list<non-empty-string>> $form
     *
     * @return non-empty-list<string>|null the override's MRZ text for each part, null when the words do not fit
     */
    private static function groups(array $identifiers, array $form): ?array
    {
        $secondaryParts = \array_slice($form, 1);
        if (\count($identifiers) !== ($secondaryParts === [] ? 1 : 2)) {
            return null;
        }

        $groups = [$identifiers[0]];
        $words = \explode('<', $identifiers[1] ?? '');
        foreach ($secondaryParts as $variants) {
            $groups[] = \implode('<', \array_splice($words, 0, \substr_count(self::key($variants[0]), '<') + 1));
        }

        return $secondaryParts === [] || $words === [] ? $groups : null;
    }

    /**
     * @param non-empty-list<non-empty-string> $variants
     *
     * @return non-empty-string|null
     */
    private static function variantSpelled(string $group, array $variants): ?string
    {
        foreach ($variants as $variant) {
            if (self::key($variant) === $group) {
                return $variant;
            }
        }

        return null;
    }

    private static function key(string $variant): string
    {
        return self::mrzEquivalent(\str_replace(' ', '<', \strtoupper($variant)));
    }

    private static function mrzEquivalent(string $canonical): string
    {
        return \str_replace('-', '<', $canonical);
    }
}
