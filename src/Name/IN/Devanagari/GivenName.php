<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari;

use Override;

/**
 * The given name in Devanagari (`पीयूष कुमार`); a person who uses no surname writes the whole name here. See
 * {@see AbstractDevanagariNamePart} and docs/sources/IN.md "Names".
 */
final class GivenName extends AbstractDevanagariNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Indian Devanagari given name';
    }
}
