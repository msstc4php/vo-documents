<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao\Table;

/**
 * One ICAO Doc 9303 transliteration table, bound to the script it covers.
 *
 * @internal
 */
interface TransliterationTable
{
    /**
     * @return non-empty-string a regex class body such as \p{Cyrillic}, matching every letter of the script
     */
    public function script(): string;

    /**
     * @param non-empty-string $character one upper-case character of the script
     *
     * @return non-empty-list<non-empty-string>|null the spellings, default first; null when the table has no entry
     */
    public function spellings(string $character): ?array;

    /**
     * @param string $character one upper-case character
     *
     * @return bool whether the table has a row of its own for the character (no fallback, no A-Z)
     */
    public function lists(string $character): bool;
}
