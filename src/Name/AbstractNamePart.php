<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\Transliterator;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Normalizer;
use Override;

/**
 * One part of a national personal name (a family name, a given name, …): NFC-normalised, trimmed, inner whitespace
 * collapsed, letter case kept. Every word matches {@see word()} (by default letters of {@see letters()}); words are
 * joined by one of {@see innerSeparators()}, never two in a row. Where the apostrophe is a separator, U+2019 (’) and
 * U+02BC (ʼ, the modifier letter apostrophe) are stored as U+0027 (').
 *
 * A compatibility character (NFKC differs from NFC: fullwidth `Ｓｍｉｔｈ`, `ª`, `ﬁ`) is refused: it looks like another
 * spelling of the same name, so two stored values would denote one person. Characters an ICAO table lists itself
 * (`Ĳ`, `Ŀ` of table 6.A) are kept, because the table gives them their own spelling.
 */
abstract class AbstractNamePart extends AbstractStringValueObject
{
    /**
     * Longest accepted name part, in characters after normalisation.
     */
    public const int MAX_LENGTH = 100;

    /**
     * Raw input above this many bytes is refused before it is normalised. It is far above anything that normalises to
     * MAX_LENGTH characters (100 characters of up to 4 bytes, each with combining marks, and padding whitespace).
     */
    private const int MAX_INPUT_BYTES = 10_000;

    private const array APOSTROPHES = ["\u{2019}", "\u{02BC}"];

    /**
     * @return non-empty-string a regex class body, e.g. \p{sc=Latin}
     */
    abstract protected function letters(): string;

    /**
     * @return non-empty-string the part's name in exception messages, e.g. "Russian given name"
     */
    abstract protected function label(): string;

    /**
     * @return string the characters allowed between two letters
     */
    protected function innerSeparators(): string
    {
        return "-' ";
    }

    /**
     * @return non-empty-string a regex matching one word
     */
    protected function word(): string
    {
        return '[' . $this->letters() . ']+';
    }

    #[Override]
    protected function parse(string $value): string
    {
        if (\strlen($value) > self::MAX_INPUT_BYTES) {
            throw $this->tooLong();
        }

        $separators = $this->innerSeparators();
        $normalized = UnicodeText::normalizeName(
            \str_contains($separators, "'") ? \str_replace(self::APOSTROPHES, "'", $value) : $value,
        );
        $word = '(?:' . $this->word() . ')';
        $pattern = $separators === ''
            ? '/^' . $word . '$/u'
            : '/^' . $word . '(?:[' . \preg_quote($separators, '/') . ']' . $word . ')*$/u';

        $length = \mb_strlen($normalized, 'UTF-8');
        if ($length > self::MAX_LENGTH) {
            throw $this->tooLong();
        }

        if ($length === 0) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (0 characters).', $this->label()));
        }

        if ($this->hasCompatibilityCharacter($normalized)) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (compatibility character).', $this->label()));
        }

        if (\preg_match($pattern, $normalized) !== 1) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (invalid character).', $this->label()));
        }

        return $normalized;
    }

    private function tooLong(): InvalidPersonNameException
    {
        return new InvalidPersonNameException(\sprintf('Invalid %s (too long).', $this->label()));
    }

    private function hasCompatibilityCharacter(string $normalized): bool
    {
        return \array_any(
            \mb_str_split($normalized, 1, 'UTF-8'),
            static fn (string $character): bool => Normalizer::normalize($character, Normalizer::FORM_KC) !== $character
                && !new Transliterator()->lists($character),
        );
    }
}
