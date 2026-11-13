<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use Override;

/**
 * Words joined by a hyphen or a space (patronymics of national custom,
 * Гусейн оглы; 143-FZ Art. 18 §4). No apostrophe. See docs/sources/RU.md §1.
 *
 * @see Federal Law No. 143-FZ of 15.11.1997 "Об актах гражданского состояния", Art. 18; official publication:
 *      Собрание законодательства Российской Федерации, 1997, No. 47, item 5340
 */
final class Patronymic extends AbstractRussianNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Russian patronymic';
    }

    #[Override]
    protected function innerSeparators(): string
    {
        return '- ';
    }
}
