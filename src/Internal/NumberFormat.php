<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use InvalidArgumentException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;

/**
 * A document number of `$minLength`..`$maxLength` characters of one class, read after the separators of
 * {@see Digits::SEPARATORS} are removed and ASCII letters upper-cased.
 *
 * @internal
 */
final readonly class NumberFormat
{
    /**
     * @param non-empty-string $characters a regex class body of upper-case ASCII, e.g. 0-9 or 0-9A-Z
     * @param positive-int $minLength
     * @param positive-int $maxLength
     */
    public function __construct(
        private string $characters,
        private int $minLength,
        private int $maxLength,
    ) {
        if ($maxLength < $minLength) {
            throw new InvalidArgumentException('The maximum length must not be below the minimum length.');
        }
    }

    /**
     * @param non-empty-string $document the document's name in the exception message
     *
     * @return non-empty-string
     *
     * @throws InvalidDocumentNumberException "(N characters)" for a wrong length of up to
     *                                        Digits::MAX_REPORTED_LENGTH characters, "(too long)" above it,
     *                                        "(invalid character)" for a right length with
     *                                        a character outside the class
     */
    public function parse(string $input, string $document): string
    {
        $normalized = \strtoupper(Digits::strip($input));
        if (!$this->allowsLength(\mb_strlen($normalized, 'UTF-8'))) {
            throw Digits::invalidLength($normalized, $document);
        }

        if (\preg_match('/^[' . $this->characters . ']+$/D', $normalized) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        return Text::nonEmpty($normalized);
    }

    /**
     * Whether `$input`, with the separators removed, has a length this format allows.
     */
    public function fitsLength(string $input): bool
    {
        return $this->allowsLength(\mb_strlen(Digits::strip($input), 'UTF-8'));
    }

    private function allowsLength(int $length): bool
    {
        return $length >= $this->minLength && $length <= $this->maxLength;
    }
}
