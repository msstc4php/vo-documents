<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\ID;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Nomor Pokok Wajib Pajak in either form DJP uses: the 15 digits printed `99.999.999.9-999.999`, or the 16 digits of
 * PMK 112/2022 (the NIK of a resident individual, a "0" put in front of the 15 digits for other taxpayers, an Identity
 * Number of a Business Place for a branch). All zeros are refused: DJP prints `00.000.000.0-000.000` and
 * `0000000000000000` where a form has no number (PENG-6/PJ.09/2024, items 5a and 6b). Otherwise format only: DJP
 * publishes no check digit and no meaning of the 15 digits, and a 16-digit number cannot be told from a NIK, so none of
 * its digits is read. Dots are accepted as separators besides the package's. `formatted()` is DJP's form for 15 digits;
 * no display form of 16 digits is quoted, so it gives the digits. `masked()` keeps the last four digits (no official
 * masking rule). The 15-digit form and its 16-digit `0`-prefixed form are distinct values: no conversion is done.
 *
 * @see https://www.pajak.go.id/sites/default/files/2024-02/PENG-6-2024%20PENGGUNAAN%20NOMOR%20POKOK%20WAJIB%20PAJAK%20PADA%20SISTEM%20ADMINISTRASI%20PERPAJAKAN.pdf
 *      DJP, PENG-6/PJ.09/2024
 * @see https://www.pajak.go.id/sites/default/files/2022-07/SP-43%202022%20NPWP%20Format%20Baru%20PMK%20112%202022.pdf
 *      DJP, SP-43/2022
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/ID.md "NPWP"
 */
final class TaxpayerNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 16;

    private const string DOCUMENT = 'Indonesian taxpayer number';

    private const string SEPARATORS = Digits::SEPARATORS . '.';

    private const int LEGACY_LENGTH = 15;

    private const int LENGTH = 16;

    public function isFifteenDigits(): bool
    {
        return \strlen($this->value) === self::LEGACY_LENGTH;
    }

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        if (!$this->isFifteenDigits()) {
            return Text::nonEmpty($this->value);
        }

        return \sprintf(
            '%s.%s.%s.%s-%s.%s',
            \substr($this->value, 0, 2),
            \substr($this->value, 2, 3),
            \substr($this->value, 5, 3),
            \substr($this->value, 8, 1),
            \substr($this->value, 9, 3),
            \substr($this->value, 12, 3),
        );
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::strip($value, self::SEPARATORS);
        $number = Digits::exactlyStripped(
            $digits,
            Digits::strippedLength($digits) === self::LEGACY_LENGTH ? self::LEGACY_LENGTH : self::LENGTH,
            self::DOCUMENT,
        );

        if (\trim($number, '0') === '') {
            $message = \sprintf('Invalid %s (placeholder, not a number).', self::DOCUMENT);

            throw new InvalidDocumentNumberException($message);
        }

        return $number;
    }
}
