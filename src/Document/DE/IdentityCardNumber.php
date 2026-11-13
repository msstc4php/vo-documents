<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Serial number of the Personalausweis: a four-character authority code and a five-character random number, nine
 * characters from `C F G H J K L M N P R T V W X Y Z` and `1-9` (no vowels, no `0`). The number has no check digit of
 * its own; the check digits are MRZ fields. `masked()` keeps the last four characters (no official masking rule):
 * `*****1T47`. The provisional and replacement identity cards (one letter and seven digits) are not covered.
 *
 * @see https://www.gesetze-im-internet.de/pauswg/__2.html PAuswG § 2 Abs. 8
 * @see https://www.gesetze-im-internet.de/pauswv/anlage_3.html PAuswV Anlage 3 Abschnitt 1 Nr. 9
 * @see docs/sources/DE.md "Personalausweis number"
 */
final class IdentityCardNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'German identity card number';

    /**
     * @return non-empty-string the Behördenkennzahl, characters 1-4
     */
    public function authorityCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 4));
    }

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
