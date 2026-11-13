<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * Letters of the Russian alphabet only (А-Я, Ё), words joined by a hyphen (double surname, 143-FZ Art. 18 §1), an
 * apostrophe or a space (surnames of foreign origin, д'Артаньян). See docs/sources/RU.md §1.
 *
 * @see https://legalacts.ru/doc/federalnyi-zakon-ot-15111997-n-143-fz-ob/ Federal Law No. 143-FZ of 15.11.1997, Art. 18
 */
final class FamilyName extends AbstractNamePart
{
    #[Override]
    protected function letters(): string
    {
        return RussianTransliteration::LETTERS;
    }

    #[Override]
    protected function label(): string
    {
        return 'Russian family name';
    }
}
