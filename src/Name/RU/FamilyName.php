<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use Override;

/**
 * Words joined by a hyphen (double surname, 143-FZ Art. 18 §1), an
 * apostrophe or a space (surnames of foreign origin, д'Артаньян). See docs/sources/RU.md §1.
 *
 * @see Federal Law No. 143-FZ of 15.11.1997 "Об актах гражданского состояния", Art. 18; official publication:
 *      Собрание законодательства Российской Федерации, 1997, No. 47, item 5340
 */
final class FamilyName extends AbstractRussianNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Russian family name';
    }
}
