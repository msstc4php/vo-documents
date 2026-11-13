<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Steuerliche Identifikationsnummer (IdNr): ten digits and a check digit. Format only: the check digit is not
 * verified because no official algorithm is published (neither the StIdV nor the BZSt states one), and the structure
 * rules of an unpublished BZSt paper (no leading zero, a repeated digit) are not applied. `masked()` keeps the last
 * four digits (no official masking rule): `*******8901`.
 *
 * @see https://www.gesetze-im-internet.de/stidv/__1.html StIdV § 1
 * @see https://www.gesetze-im-internet.de/ao_1977/__139b.html AO § 139b
 * @see docs/sources/DE.md "Steuerliche Identifikationsnummer"
 */
final class TaxIdentificationNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'German tax identification number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 11, self::DOCUMENT);
    }
}
