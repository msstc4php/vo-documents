<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao;

use InvalidArgumentException;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Latin personal name as written in an ICAO Doc 9303 MRZ, e.g. VAN<DER<BERG<<JAN<PIETER: A-Z, `-` and `<`; `<<`
 * separates the primary identifier from the secondary identifiers and `<` stands for a space. Hyphens are kept
 * (visual zone form) and become `<` only in {@see mrzField()}. Input is upper-cased and spaces become `<`.
 *
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p4_cons_en.pdf ICAO Doc 9303 Part 4,
 *       8th ed. (2021), §4.2.2-§4.2.3 (name field truncation)
 */
final class PersonName extends AbstractStringValueObject
{
    /**
     * Longest accepted canonical form, in characters.
     */
    public const int MAX_LENGTH = 255;

    private const string IDENTIFIER = '[A-Z]+(?:[<-][A-Z]+)*';

    /**
     * @param list<string> $secondary
     *
     * @throws InvalidPersonNameException
     */
    public static function fromParts(string $primary, array $secondary = []): self
    {
        foreach ([$primary, ...$secondary] as $part) {
            if (\str_contains($part, '<')) {
                throw new InvalidPersonNameException('Invalid ICAO personal name.');
            }
        }

        $value = self::canonical($primary);
        if ($secondary !== []) {
            $value .= '<<' . \implode('<', \array_map(self::canonical(...), $secondary));
        }

        return new self($value);
    }

    /**
     * @return non-empty-string
     */
    public function primaryIdentifier(): string
    {
        return \str_replace('<', ' ', $this->identifiers()[0]);
    }

    /**
     * @return list<non-empty-string>
     */
    public function secondaryIdentifiers(): array
    {
        $identifiers = \preg_split('/</', $this->identifiers()[1] ?? '', -1, \PREG_SPLIT_NO_EMPTY);

        return $identifiers === false ? [] : $identifiers;
    }

    /**
     * @return non-empty-string e.g. RIMSKY-KORSAKOV, NIKOLAI ANDREEVICH
     */
    public function visualZone(): string
    {
        $secondary = $this->secondaryIdentifiers();

        return $secondary === []
            ? $this->primaryIdentifier()
            : $this->primaryIdentifier() . ', ' . \implode(' ', $secondary);
    }

    /**
     * The MRZ name field, padded with `<` or truncated per Doc 9303 Part 4 §4.2.3; the deterministic truncation
     * procedure is described in docs/sources/ICAO.md.
     *
     * @param int<2, max> $width 39 for TD3 (passport)
     *
     * @return non-empty-string
     */
    public function mrzField(int $width = 39): string
    {
        if ($width < 2) {
            throw new InvalidArgumentException('The MRZ name field width must be at least 2.');
        }

        [$primary, $secondary] = $this->identifiers();
        $primary = \str_replace('-', '<', $primary);
        if ($secondary === null) {
            return \str_pad($this->cut($primary, $width), $width, '<');
        }

        if ($width >= 4) {
            // Keeps room for << and the first secondary character (Part 4 §4.2.2); a shorter primary is unchanged.
            $primary = $this->cut($primary, $width - 3);
        }

        return \str_pad($this->cut($primary . '<<' . \str_replace('-', '<', $secondary), $width), $width, '<');
    }

    #[Override]
    protected function parse(string $value): string
    {
        $canonical = self::canonical($value);
        if (\strlen($canonical) > self::MAX_LENGTH || \preg_match('/^' . self::IDENTIFIER . '(?:<<' . self::IDENTIFIER . ')?$/', $canonical) !== 1) {
            throw new InvalidPersonNameException('Invalid ICAO personal name.');
        }

        return $canonical;
    }

    private static function canonical(string $part): string
    {
        return \str_replace(' ', '<', \strtoupper(UnicodeText::normalizeName($part)));
    }

    /**
     * @return array{non-empty-string, non-empty-string|null} the primary and the secondary identifiers
     */
    private function identifiers(): array
    {
        if (\preg_match('/(' . self::IDENTIFIER . ')(?:<<(' . self::IDENTIFIER . '))?/', $this->value, $match) !== 1) {
            throw new LogicException('A parsed ICAO personal name always matches.');
        }

        return [$match[1], $match[2] ?? null];
    }

    /**
     * First $width characters of $text ending with a letter: when the cut would end on a filler, the nearest earlier
     * component of two or more letters loses its last letter and the cut is retried.
     *
     * @param positive-int $width
     */
    private function cut(string $text, int $width): string
    {
        while (true) {
            $field = \substr($text, 0, $width);
            if (!\str_ends_with($field, '<')) {
                return $field;
            }

            if (\preg_match('/.*[A-Z]{2}(?![A-Z])/', $field, $match) !== 1) {
                return \rtrim($field, '<');
            }

            $text = \substr_replace($text, '', \strlen($match[0]) - 1, 1);
        }
    }
}
