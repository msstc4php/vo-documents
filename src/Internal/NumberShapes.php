<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

use InvalidArgumentException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;

/**
 * A document number that has one of several fixed shapes (e.g. a letter and eight digits, or nine digits), read after
 * the separators of {@see Digits::SEPARATORS} are removed and ASCII letters upper-cased.
 *
 * A shape is a run of atoms, each an upper-case letter, a digit or a `[...]` class, optionally repeated `{n}` times;
 * its length is computed from the atoms, so it cannot drift from the pattern. A negated `[^...]` class is refused: it
 * would admit any other character, punctuation and multibyte letters included.
 *
 * @internal
 */
final readonly class NumberShapes
{
    private const string ATOM = '/\G(?:[A-Z0-9]|\[(?!\^)[^\[\]\\\\]+\])(?:\{([1-9][0-9]*)\})?/';

    private string $pattern;

    /** @var list<int> */
    private array $lengths;

    /**
     * @param non-empty-string $shape
     * @param non-empty-string ...$moreShapes
     *
     * @throws InvalidArgumentException for a shape outside the atom syntax above
     */
    public function __construct(string $shape, string ...$moreShapes)
    {
        $shapes = [$shape, ...$moreShapes];
        $lengths = [];
        foreach ($shapes as $each) {
            $lengths[] = $this->lengthOf($each);
        }

        $this->lengths = $lengths;
        $this->pattern = '/^(?:' . \implode('|', $shapes) . ')$/D';
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
        $normalized = Digits::upperCasedOfLength($input, $this->allowsLength(...), $document);
        if (\preg_match($this->pattern, $normalized) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', $document));
        }

        return Text::nonEmpty($normalized);
    }

    /**
     * Whether `$input`, with the separators removed, has a length one of the shapes has.
     */
    public function fitsLength(string $input): bool
    {
        return $this->allowsLength(Digits::strippedLength($input));
    }

    private function lengthOf(string $shape): int
    {
        $offset = 0;
        $length = 0;
        do {
            if (\preg_match(self::ATOM, $shape, $atom, 0, $offset) !== 1) {
                throw new InvalidArgumentException('A shape is a run of letters, digits and [...] classes with {n}.');
            }

            $offset += \strlen($atom[0]);
            $length += (int) ($atom[1] ?? 1);
        } while ($offset < \strlen($shape));

        return $length;
    }

    private function allowsLength(int $length): bool
    {
        return \in_array($length, $this->lengths, true);
    }
}
