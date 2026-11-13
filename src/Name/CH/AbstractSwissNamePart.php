<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\CH;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Letters of the register character set "ISO-Norm 8859-1 + Latin Extended-A": `A-Z`, `a-z`, the letters `À-Ö Ø-ö ø-ÿ`
 * and U+0100-U+017F, which hold the letters of German, French, Italian and Romansh (`ä ö ü é è à ç`). Words are joined
 * by a hyphen (`Müller-Brunner`), an apostrophe (`D'Amico`) or a space (`de Weck`). See docs/sources/CH.md "Names".
 *
 * @see https://www.fedlex.admin.ch/eli/cc/2010/96/de SR 143.111 (Verordnung des EJPD über die Ausweise), Art. 4, 5
 *      and 22
 * @see https://www.fedlex.admin.ch/eli/oc/2024/335/de Zivilstandsverordnung, Änderung vom 26. Juni 2024, Art. 80
 */
abstract class AbstractSwissNamePart extends AbstractLatinNamePart
{
    #[Override]
    final protected function letters(): string
    {
        return 'A-Za-zÀ-ÖØ-öø-ÿĀ-ſ';
    }
}
