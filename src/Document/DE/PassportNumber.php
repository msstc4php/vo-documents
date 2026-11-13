<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Serial number of the Reisepass (also the Dienstpass and Diplomatenpass): nine characters from
 * `C F G H J K L M N P R T V W X Y Z` and `1-9` (no vowels, no `0`), the authority code followed by a random number.
 * The acts give no length for the authority code, so it is not exposed. The number has no check digit of its own;
 * the check digits are MRZ fields. `masked()` keeps the last four characters (no official masking rule): `*****1T47`.
 * Provisional passports (one letter and seven digits) are not covered.
 *
 * @see https://www.gesetze-im-internet.de/pa_g_1986/__4.html PassG § 4 Abs. 2 Satz 2 Nr. 5
 * @see https://www.gesetze-im-internet.de/passv_2007/anlage_11.html PassV Anlage 11 Nr. 9 and Tabelle 1 ("Pass-Nr.",
 *      9 Zeichen)
 * @see docs/sources/DE.md "Reisepass number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'German passport number';

    #[Override]
    public function masked(): string
    {
        return SerialNumberFormat::masked(Text::nonEmpty($this->value));
    }

    #[Override]
    protected function parse(string $value): string
    {
        return SerialNumberFormat::parse($value, self::DOCUMENT);
    }
}
