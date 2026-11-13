<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Health and Care number (H&C, Northern Ireland): an NHS-format number, `320 000 0015`, whose first nine digits lie in
 * the range reserved for Northern Ireland, 320 000 001 to 399 999 999; the tenth is the NHS Modulus 11 check digit,
 * verified. `masked()` keeps the last four digits of `formatted()` (no official masking rule): `*** *** 0015`.
 *
 * @see https://www.datadictionary.nhs.uk/attributes/health_and_care_number.html NHS Data Model and Dictionary, HEALTH
 *      AND CARE NUMBER (read through the Internet Archive copy of 2025-09-10)
 * @see docs/sources/GB.md "Health and Care number, Northern Ireland"
 */
final class HealthAndCareNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Northern Ireland Health and Care number';

    private const int FIRST = 320_000_001;

    private const int LAST = 399_999_999;

    /**
     * @return non-empty-string e.g. "320 000 0015"
     */
    public function formatted(): string
    {
        return \sprintf('%s %s %s', \substr($this->value, 0, 3), \substr($this->value, 3, 3), \substr($this->value, 6));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 10, self::DOCUMENT);
        $base = (int) \substr($digits, 0, 9);
        if ($base < self::FIRST || $base > self::LAST) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (range).', self::DOCUMENT));
        }

        if (!NhsCheckDigit::isValid($digits)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $digits;
    }
}
