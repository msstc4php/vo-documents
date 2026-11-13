<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * Letters of the Russian alphabet only (А-Я, Ё), words joined by a hyphen (the only sign 143-FZ Art. 18 §2 allows) or
 * a space (several given names, "имя (имена)" in the MVD orders). No apostrophe. See docs/sources/RU.md §1.
 *
 * @see https://legalacts.ru/doc/federalnyi-zakon-ot-15111997-n-143-fz-ob/ Federal Law No. 143-FZ of 15.11.1997, Art. 18
 */
final class GivenName extends AbstractNamePart
{
    #[Override]
    protected function letters(): string
    {
        return RussianTransliteration::LETTERS;
    }

    #[Override]
    protected function label(): string
    {
        return 'Russian given name';
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return '- ';
    }
}
