<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CH;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * AHV-Nummer (AVS, Sozialversicherungsnummer): 13 digits, the country code `756`, a random nine-digit number and a
 * check digit, displayed `756.3047.5009.62`. The check digit completes the sum of the first twelve digits, weighted
 * 1 and 3 alternately from the left (3 on the twelfth), to a multiple of 10. The dots of the display form are
 * accepted as separators. No data is encoded. `masked()` keeps the last four digits of `formatted()` (no official
 * masking rule): `***.****.**09.62`.
 *
 * @see https://sozialversicherungen.admin.ch/de/d/6938/download BSV, Wegleitung über Versicherungsausweis und
 *      individuelles Konto (WL VA/IK), 318.106.02 d, Rz 1201-1202 and Anhang 7
 * @see docs/sources/CH.md "AHV number"
 */
final class AhvNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Swiss AHV number';

    private const string COUNTRY_CODE = '756';

    private const array WEIGHTS = [1, 3, 1, 3, 1, 3, 1, 3, 1, 3, 1, 3, 1];

    /**
     * @return non-empty-string e.g. "756.3047.5009.62"
     */
    public function formatted(): string
    {
        return \sprintf(
            '%s.%s.%s.%s',
            \substr($this->value, 0, 3),
            \substr($this->value, 3, 4),
            \substr($this->value, 7, 4),
            \substr($this->value, 11),
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
        $digits = Digits::exactly(\str_replace('.', '', $value), 13, self::DOCUMENT);
        if (!\str_starts_with($digits, self::COUNTRY_CODE)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (country code).', self::DOCUMENT));
        }

        if (Checksum::weightedMod($digits, self::WEIGHTS, 10) !== 0) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $digits;
    }
}
