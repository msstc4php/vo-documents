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
 * The whole part must be written in the script of one table; spaces and hyphens are kept.
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6
 */
final readonly class Transliterator
{
    /** Bounds variants() on untrusted input: 4096 = eight two-way or seven three-way characters. */
    private const int MAX_VARIANTS = 4096;

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

        return $variants;
    }

    /**
     * @return non-empty-list<non-empty-list<non-empty-string>> the spellings of each character, in order
     *
     * @throws InvalidPersonNameException
     */
    private function segments(string $nationalPart): array
    {
        $text = UnicodeText::normalizeName($nationalPart);
        $letters = \str_replace([' ', '-'], '', $text);
        if ($letters === '') {
            throw new InvalidPersonNameException('Invalid personal name (empty).');
        }

        $table = $this->tableFor($letters);
        $segments = [];
        foreach (\mb_str_split(\mb_strtoupper($text)) as $character) {
            $spellings = $character === ' ' || $character === '-' ? [$character] : $table->spellings($character);
            if ($spellings === null) {
                throw new InvalidPersonNameException('Invalid personal name (no ICAO transliteration for a character).');
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

        throw new InvalidPersonNameException('Invalid personal name (no ICAO transliteration for a character).');
    }
}
