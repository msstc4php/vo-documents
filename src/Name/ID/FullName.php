<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ID;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * The "Nama Lengkap" Dukcapil records as one field (`Siti Nurhaliza`, `Suharto`, `Moh. Hatta`): Latin letters, words
 * joined by a hyphen, an apostrophe or a space, and a full stop after or between letters of a word, as abbreviations
 * and initials are written. Permendagri 73/2022 asks a new record for two words and at most 60 characters (Art. 4(2)),
 * no digits or punctuation (Art. 5(3)), and says names recorded before it "tetap berlaku" (Art. 8): none of that
 * is enforced, since a recorded name must stay valid. See docs/sources/ID.md "Names".
 *
 * @see https://pasal.id/peraturan/permen/permendagri-no-73-tahun-2022 Permendagri 73/2022, Art. 4, 5, 8 (quoted from)
 */
final class FullName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Indonesian full name';
    }

    #[Override]
    protected function word(): string
    {
        return $this->wordWithAbbreviationDots();
    }
}
