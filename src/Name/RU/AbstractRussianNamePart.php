<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\RU;

use MSSTC4PHP\ValueObject\Documents\Name\AbstractNamePart;
use Override;

/**
 * The 33 letters of the Russian alphabet; no word starts with Ъ or Ь (Russian orthography, and Ь has no Latin
 * spelling to start an identifier with). See docs/sources/RU.md §1.
 *
 * @see Federal Law No. 143-FZ of 15.11.1997 "Об актах гражданского состояния", Art. 18; official publication:
 *      Собрание законодательства Российской Федерации, 1997, No. 47, item 5340
 */
abstract class AbstractRussianNamePart extends AbstractNamePart
{
    #[Override]
    final protected function letters(): string
    {
        return 'А-ЯЁа-яё';
    }

    #[Override]
    final protected function word(): string
    {
        return '[А-ЩЫЭ-ЯЁа-щыэ-яё][А-ЯЁа-яё]*';
    }
}
