<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IN\Devanagari;

use Override;

/**
 * The surname in Devanagari (`जैन`, `राय चौधरी`). See {@see AbstractDevanagariNamePart} and docs/sources/IN.md "Names".
 */
final class Surname extends AbstractDevanagariNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Indian Devanagari surname';
    }
}
