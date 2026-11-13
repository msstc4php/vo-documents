<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CH;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Nummer des Führerausweises (field 5), assigned by the federal system IVZ for every canton: 12 digits on the
 * credit-card licence issued from 15 April 2023, the nine-digit FABER-PIN and a three-digit running number
 * (`123456789004`); 15 digits on the licence issued from 1 April 2003 to 14 April 2023 (specimen `000123456789003`),
 * whose layout is not published. Format only: no check digit is published. `masked()` keeps the last four digits (no
 * official masking rule): `********9004`.
 *
 * @see http://web.archive.org/web/20250623182354/https://www.astra.admin.ch/dam/astra/de/dokumente/dokumente-strassenverkehr/weisungen/fuehrerausweis-kreditkartenformat.pdf.download.pdf/Weisungen%20betreffend%20die%20Ausstellung%20von%20Lernfahr-%20und%20F%C3%BChrerausweisen.pdf
 *      ASTRA, Weisungen betreffend die Ausstellung von Lernfahr- und Führerausweisen, 15 April 2023, Ziffer 5,
 *      Anhang 1 and 2 (Internet Archive copy of 2025-06-23)
 * @see docs/sources/CH.md "Driving licence number"
 */
final class DriverLicenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Swiss driving licence number';

    private const int LENGTH = 12;

    private const int LEGACY_LENGTH = 15;

    /**
     * @return non-empty-string|null digits 1-9 of a 12-digit number, null for the 15-digit layout of 2003
     */
    public function faberPin(): ?string
    {
        return \strlen($this->value) === self::LENGTH ? Text::nonEmpty(\substr($this->value, 0, 9)) : null;
    }

    /**
     * @return non-empty-string|null the running number (digits 10-12) of a 12-digit number, null for the 15-digit
     *                               layout of 2003
     */
    public function sequenceNumber(): ?string
    {
        return \strlen($this->value) === self::LENGTH ? Text::nonEmpty(\substr($this->value, 9)) : null;
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $stripped = Digits::strip($value);
        $length = \mb_strlen($stripped, 'UTF-8');
        if ($length !== self::LENGTH && $length !== self::LEGACY_LENGTH) {
            throw Digits::invalidLength($stripped, self::DOCUMENT);
        }

        return Digits::exactly($stripped, $length, self::DOCUMENT);
    }
}
