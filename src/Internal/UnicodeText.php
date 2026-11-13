<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use Normalizer;

/**
 * @internal
 */
final class UnicodeText
{
    public static function normalizeName(string $input): string
    {
        $composed = Normalizer::normalize($input, Normalizer::FORM_C);
        if (!\is_string($composed)) {
            throw new InvalidPersonNameException('Invalid personal name (not valid UTF-8).');
        }

        return \trim(\preg_replace('/[\p{Z}\s]+/u', ' ', $composed) ?? $composed);
    }

    /**
     * @param non-empty-string $letterClass a regex class body such as \p{Cyrillic}
     */
    public static function isInScript(string $text, string $letterClass): bool
    {
        return \preg_match('/^[' . $letterClass . ']+$/u', $text) === 1;
    }
}
