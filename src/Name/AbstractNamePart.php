<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Normalizer;
use Override;

/**
 * One part of a national personal name (a family name, a given name, …): NFC-normalised, trimmed, inner whitespace
 * collapsed, letter case kept. Every word matches {@see word()} (by default letters of {@see letters()}); words are
 * joined by one of {@see innerSeparators()}, never two in a row. Where the apostrophe is a separator, U+2019 (’) is
 * stored as U+0027 (').
 *
 * A compatibility character (NFKC differs from NFC: fullwidth `Ｓｍｉｔｈ`, `ª`, the ligature `Ĳ`) is refused: it
 * looks like another spelling of the same name, so two stored values would denote one person.
 */
abstract class AbstractNamePart extends AbstractStringValueObject
{
    /**
     * Longest accepted name part, in characters after normalisation.
     */
    public const int MAX_LENGTH = 100;

    /**
     * @return non-empty-string a regex class body, e.g. \p{Latin}
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
        $separators = $this->innerSeparators();
        $normalized = UnicodeText::normalizeName(
            \str_contains($separators, "'") ? \str_replace("\u{2019}", "'", $value) : $value,
        );
        $word = '(?:' . $this->word() . ')';
        $pattern = $separators === ''
            ? '/^' . $word . '$/u'
            : '/^' . $word . '(?:[' . \preg_quote($separators, '/') . ']' . $word . ')*$/u';

        $length = \mb_strlen($normalized, 'UTF-8');
        if ($length === 0 || $length > self::MAX_LENGTH) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (%d characters).', $this->label(), $length));
        }

        if (Normalizer::normalize($normalized, Normalizer::FORM_KC) !== $normalized) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (compatibility character).', $this->label()));
        }

        if (\preg_match($pattern, $normalized) !== 1) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (invalid character).', $this->label()));
        }

        return $normalized;
    }
}
