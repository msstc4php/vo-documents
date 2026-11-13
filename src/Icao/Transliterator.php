<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\Cyrillic;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\LatinMultinational;
use MSSTC4PHP\ValueObject\Documents\Icao\Table\TransliterationTable;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;

/**
 * Latin spelling of a national name part per the ICAO Doc 9303 transliteration tables (see docs/sources/ICAO.md).
 * The whole part must be written in the script of one table; spaces and hyphens are kept, apostrophes omitted.
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6
 */
final readonly class Transliterator
{
    /** Bounds variants() on untrusted input: 4096 = eight two-way or seven three-way characters. */
    private const int MAX_VARIANTS = 4096;

    /** Omitted without a filler, Doc 9303 Part 3 §4.6 ("D’ARTAGNAN" → "DARTAGNAN"). */
    private const array APOSTROPHES = ["'", "\u{2019}"];

    private const string NO_SPELLING = 'Invalid personal name (no ICAO transliteration for a character).';

    /** @var non-empty-list<TransliterationTable> */
    private array $tables;

    public function __construct()
    {
        $this->tables = [new LatinMultinational(), new Cyrillic()];
    }

    /**
     * @return non-empty-string the default spelling, upper case
     *
     * @throws InvalidPersonNameException when a character has no transliteration or the scripts are mixed
     */
    public function toLatin(string $nationalPart): string
    {
        return \implode('', \array_map(
            static fn (array $spellings): string => $spellings[0],
            $this->segments($nationalPart),
        ));
    }

    /**
     * @return non-empty-list<non-empty-string> every spelling the tables allow, the default first
     *
     * @throws InvalidPersonNameException when a character has no transliteration, the scripts are mixed or the
     *                                    spellings exceed {@see self::MAX_VARIANTS}
     */
    public function variants(string $nationalPart): array
    {
        $segments = $this->segments($nationalPart);

        $count = 1;
        foreach ($segments as $spellings) {
            $count *= \count($spellings);
            if ($count > self::MAX_VARIANTS) {
                throw new InvalidPersonNameException('Invalid personal name (too many transliteration variants).');
            }
        }

        $variants = \array_shift($segments);
        foreach ($segments as $spellings) {
            $next = [];
            foreach ($variants as $prefix) {
                foreach ($spellings as $spelling) {
                    $next[] = $prefix . $spelling;
                }
            }

            $variants = $next;
        }

        // Different characters can share a spelling (Å = AA or A, then A): keep the first occurrence.
        return \array_values(\array_unique($variants));
    }

    /**
     * @return non-empty-list<non-empty-list<non-empty-string>> the spellings of each character, in order
     *
     * @throws InvalidPersonNameException
     */
    private function segments(string $nationalPart): array
    {
        $text = UnicodeText::normalizeName(\str_replace(self::APOSTROPHES, '', $nationalPart));
        $letters = \str_replace([' ', '-'], '', $text);
        if ($letters === '') {
            throw new InvalidPersonNameException('Invalid personal name (empty).');
        }

        $table = $this->tableFor($letters);
        $segments = [];
        foreach (\mb_str_split(\mb_strtoupper($text)) as $character) {
            $spellings = $character === ' ' || $character === '-' ? [$character] : $table->spellings($character);
            if ($spellings === null) {
                throw new InvalidPersonNameException(self::NO_SPELLING);
            }

            $segments[] = $spellings;
        }

        \assert($segments !== []);

        return $segments;
    }

    /**
     * @param non-empty-string $letters
     *
     * @throws InvalidPersonNameException
     */
    private function tableFor(string $letters): TransliterationTable
    {
        $scripts = [];
        foreach ($this->tables as $table) {
            if (UnicodeText::isInScript($letters, $table->script())) {
                return $table;
            }

            $scripts[] = $table->script();
        }

        if (UnicodeText::isInScript($letters, \implode('', $scripts))) {
            throw new InvalidPersonNameException('Invalid personal name (mixed scripts).');
        }

        throw new InvalidPersonNameException(self::NO_SPELLING);
    }
}
