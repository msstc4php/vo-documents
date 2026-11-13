<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * One part of a national personal name (a family name, a given name, …): NFC-normalised, trimmed, inner whitespace
 * collapsed, letter case kept. Every word matches {@see word()} (by default letters of {@see letters()}); words are
 * joined by one of {@see innerSeparators()}, never two in a row. Where the apostrophe is a separator, U+2019 (’) is
 * stored as U+0027 (').
 */
abstract class AbstractNamePart extends AbstractStringValueObject
{
    /**
     * Longest accepted name part, in characters after normalisation.
     */
    public const int MAX_LENGTH = 100;

    /**
     * @return non-empty-string a regex class body, e.g. \p{Cyrillic}
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

        if (\mb_strlen($normalized) > self::MAX_LENGTH || \preg_match($pattern, $normalized) !== 1) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (%d characters).', $this->label(), \mb_strlen($normalized)));
        }

        return $normalized;
    }
}
