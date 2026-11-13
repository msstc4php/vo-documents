<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * One part of a national personal name (a family name, a given name, …): NFC-normalised, trimmed, inner whitespace
 * collapsed, letter case kept. Every word starts and ends with a letter of {@see letters()}; words are joined by one
 * of {@see innerSeparators()}, never two in a row.
 */
abstract class AbstractNamePart extends AbstractStringValueObject
{
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

    #[Override]
    protected function parse(string $value): string
    {
        $normalized = UnicodeText::normalizeName($value);
        $letters = $this->letters();
        $word = '[' . $letters . ']+';
        $separators = $this->innerSeparators();
        $pattern = $separators === ''
            ? '/^' . $word . '$/u'
            : '/^' . $word . '(?:[' . \preg_quote($separators, '/') . ']' . $word . ')*$/u';

        if (\preg_match($pattern, $normalized) !== 1) {
            throw new InvalidPersonNameException(\sprintf('Invalid %s (%d characters).', $this->label(), \mb_strlen($normalized)));
        }

        return $normalized;
    }
}
