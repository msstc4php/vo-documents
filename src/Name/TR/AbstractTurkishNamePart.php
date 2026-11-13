<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\TR;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Letters of the Turkish alphabet of Law 1353 (29 letters: no `Q`, `W`, `X`; dotless `I ı` and dotted `İ i` are
 * different letters, kept as typed and never re-cased) and the circumflex vowels `Â â Î î Û û` the register writes;
 * words are joined by a space only (`Ahmet Can`, `Yılmaz Kaya`). See docs/sources/TR.md "Names".
 *
 * @see https://www.mevzuat.gov.tr/MevzuatMetin/1.3.1353.pdf Law 1353 (Türk Harflerinin Kabul ve Tatbiki Hakkında
 *      Kanun), Madde 1, 3 and Merbut Cetvel
 * @see https://www.mevzuat.gov.tr/MevzuatMetin/1.5.5490.pdf Law 5490 (Nüfus Hizmetleri Kanunu), Geçici Madde 8
 */
abstract class AbstractTurkishNamePart extends AbstractLatinNamePart
{
    #[Override]
    final protected function letters(): string
    {
        return 'A-PR-VYZa-pr-vyzÇçĞğİıÖöŞşÜüÂâÎîÛû';
    }

    #[Override]
    final protected function innerSeparators(): string
    {
        return ' ';
    }
}
