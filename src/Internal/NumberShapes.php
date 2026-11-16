<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;

/**
 * A document number that has one of several fixed shapes (e.g. a letter and eight digits, or nine digits), read after
 * the separators of {@see Digits::SEPARATORS} are removed and ASCII letters upper-cased.
 *
 * @internal
 */
final readonly class NumberShapes
{
    /**
     * @param non-empty-array<non-empty-string, positive-int> $shapes each shape's unanchored regex over upper-case
     *                                                                ASCII, mapped to the one length it matches
     */
    public function __construct(private array $shapes)
    {
    }

    /**
     * @param non-empty-string $document the document's name in the exception message
     *
     * @return non-empty-string
     *
     * @throws InvalidDocumentNumberException "(N characters)" for a length no shape has (up to
     *                                        Digits::MAX_REPORTED_LENGTH characters, "(too long)" above it),
     *                                        "(invalid character)" for a length some shape has but no shape matches
     */
    public function parse(string $input, string $document): string
    {
        $normalized = \strtoupper(Digits::strip($input));
        if (!$this->allowsLength(\mb_strlen($normalized, 'UTF-8'))) {
            throw Digits::invalidLength($normalized, $document);
        }

        $pattern = '/^(?:' . \implode('|', \array_keys($this->shapes)) . ')$/D';
        if (\preg_match($pattern, $normalized) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        return Text::nonEmpty($normalized);
    }

    /**
     * Whether `$input`, with the separators removed, has a length one of the shapes has.
     */
    public function fitsLength(string $input): bool
    {
        return $this->allowsLength(\mb_strlen(Digits::strip($input), 'UTF-8'));
    }

    private function allowsLength(int $length): bool
    {
        return \in_array($length, $this->shapes, true);
    }
}
