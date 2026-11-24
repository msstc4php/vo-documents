<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\DE;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Steuerliche Identifikationsnummer (IdNr): ten digits and a check digit. `from()` checks the format only (1.x never
 * refuses a number 1.0 accepted); {@see hasValidCheckDigit()} reports the ELSTER check digit. The ELSTER structure
 * rules (no leading zero, one digit twice or three times, a tripled digit never on adjacent positions) are not
 * applied: one of its own example numbers breaks the last one (DE.md). `masked()` keeps the last four digits (no
 * official masking rule): `*******8901`.
 *
 * @see https://download.elster.de/download/schnittstellen/Pruefung_der_Steuer_und_Steueridentifikatsnummer.pdf
 *      ELSTER, "Prüfung der Steuer- und Steueridentifikationsnummer", § 2.2
 * @see https://www.gesetze-im-internet.de/stidv/__1.html StIdV § 1
 * @see https://www.gesetze-im-internet.de/ao_1977/__139b.html AO § 139b
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/DE.md "Steuerliche Identifikationsnummer"
 */
final class TaxIdentificationNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 11;

    private const string DOCUMENT = 'German tax identification number';

    /**
     * Whether the eleventh digit is the ISO 7064 MOD 11,10 check digit of the first ten, as ELSTER § 2.2 computes it.
     * Report only: a number that fails is still accepted. ELSTER forbids telling the holder the correct check digit,
     * so none is exposed.
     */
    public function hasValidCheckDigit(): bool
    {
        return Checksum::iso7064Mod11Dash10(\substr($this->value, 0, 10)) === (int) $this->value[10];
    }

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
