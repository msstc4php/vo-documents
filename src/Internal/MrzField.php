<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * A run of MRZ characters, addressed by the 1-based line and character position the Doc 9303 tables use.
 *
 * @internal
 */
final readonly class MrzField
{
    /**
     * @param positive-int $line
     * @param positive-int $position
     * @param positive-int $length
     */
    public function __construct(
        public int $line,
        public int $position,
        public int $length,
    ) {
    }

    /**
     * @param list<string> $lines
     */
    public function of(array $lines): string
    {
        return \substr($lines[$this->line - 1], $this->position - 1, $this->length);
    }
}
