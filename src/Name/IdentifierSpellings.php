<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use LogicException;

/**
 * The ICAO spellings of one identifier of a national name (the primary identifier, or all secondary identifiers) word
 * by word, with the national separator between two words: a space or a hyphen. A word is what the MRZ separates by
 * `<`, so `Rimsky-Korsakov` has two.
 *
 * @internal
 */
final readonly class IdentifierSpellings
{
    /**
     * @param non-empty-list<non-empty-list<non-empty-string>> $words the spellings of each word
     * @param list<' '|'-'> $separators the separator after each word but the last
     */
    public function __construct(
        public array $words,
        public array $separators,
    ) {
        if (\count($separators) !== \count($words) - 1) {
            throw new LogicException('An identifier has one separator fewer than words.');
        }
    }

    /**
     * @param list<string> $mrzWords the words of the override's identifier, upper case
     *
     * @return non-empty-string|null the matching spelling with the national separators, null when a word has none
     */
    public function spell(array $mrzWords): ?string
    {
        if (\count($mrzWords) !== \count($this->words)) {
            return null;
        }

        $spelling = '';
        foreach ($this->words as $index => $variants) {
            $variant = $this->variantSpelled($mrzWords[$index], $variants);
            if ($variant === null) {
                return null;
            }

            $spelling .= $variant . ($this->separators[$index] ?? '');
        }

        return $spelling;
    }

    /**
     * @param non-empty-list<non-empty-string> $variants
     *
     * @return non-empty-string|null
     */
    private function variantSpelled(string $mrzWord, array $variants): ?string
    {
        foreach ($variants as $variant) {
            if (\strtoupper($variant) === $mrzWord) {
                return $variant;
            }
        }

        return null;
    }
}
