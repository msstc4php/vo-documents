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
 * NHS number (England and Wales): ten digits, `943 476 5919`, the tenth a Modulus 11 check digit verified as the NHS
 * Data Dictionary prescribes (weights 10 to 2; a remainder giving 10 is never issued). No range is published, so none
 * is checked. `masked()` keeps the last four digits of `formatted()` (no official masking rule): `*** *** 5919`.
 *
 * @see https://www.datadictionary.nhs.uk/attributes/nhs_number.html NHS Data Model and Dictionary, NHS NUMBER (read
 *      through the Internet Archive copy of 2025-09-14)
 * @see https://www.datadictionary.nhs.uk/attributes/health_and_care_number.html NHS Data Model and Dictionary, HEALTH
 *      AND CARE NUMBER ("3 3 4 format")
 * @see docs/sources/GB.md "NHS number"
 */
final class NhsNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'NHS number';

    /**
     * @return non-empty-string e.g. "943 476 5919"
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
        if (!NhsCheckDigit::isValid($digits)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $digits;
    }
}
