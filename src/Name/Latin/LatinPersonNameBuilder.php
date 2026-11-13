<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\Latin;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Name\IdentifierSpellings;
use MSSTC4PHP\ValueObject\Documents\Name\TransliterationVariant;

/**
 * Derives the ICAO Latin form of a name made of family parts (joined by a space into the primary identifier) and
 * given parts (one secondary identifier each), and lists its spellings word by word for an override policy.
 *
 * @internal
 */
final readonly class LatinPersonNameBuilder
{
    private Transliterator $transliterator;

    /**
     * @param non-empty-list<string> $family
     * @param list<string> $given
     */
    public function __construct(
        private array $family,
        private array $given,
    ) {
        $this->transliterator = new Transliterator();
    }

    /**
     * @throws InvalidPersonNameException when a letter has no ICAO spelling or the Latin form is too long
     */
    public function derive(): IcaoPersonName
    {
        return IcaoPersonName::fromParts(
            $this->transliterator->toLatin(\implode(' ', $this->family)),
            \array_map($this->transliterator->toLatin(...), $this->given),
        );
    }

    /**
     * Accepts only another ICAO spelling of the parts; the spellings are listed only when an override is given, and
     * the variant cap applies to each word (a given name `Jürgen Jürgen …` is not refused for its product).
     *
     * @param non-empty-string $refusal exception message for an override that is no spelling of the name
     */
    public function transliterationVariant(string $refusal): TransliterationVariant
    {
        return new TransliterationVariant(
            fn (): array => [
                $this->spellings($this->family),
                $this->given === [] ? null : $this->spellings($this->given),
            ],
            $refusal,
        );
    }

    /**
     * @param non-empty-list<string> $parts
     *
     * @throws InvalidPersonNameException when a word has no ICAO spelling or too many
     */
    private function spellings(array $parts): IdentifierSpellings
    {
        $words = [];
        $separators = [];
        foreach ($parts as $index => $part) {
            if ($index > 0) {
                $separators[] = ' ';
            }

            $tokens = \preg_split('/([ -])/', $part, -1, \PREG_SPLIT_DELIM_CAPTURE);
            foreach ($tokens === false ? [] : $tokens as $position => $token) {
                if ($position % 2 === 1) {
                    $separators[] = $token === '-' ? '-' : ' ';
                } else {
                    $words[] = $this->transliterator->variants($token);
                }
            }
        }

        \assert($words !== []);

        return new IdentifierSpellings($words, $separators);
    }
}
