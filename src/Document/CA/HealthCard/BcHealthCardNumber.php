<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * British Columbia: the MSP Personal Health Number, "a true 10-digit number with a '9' always in the first position",
 * "validated by using a MOD-11 Check Digit Routine": weights 0 2 4 8 5 10 9 7 3 on digits 1-9, and 11 minus the sum
 * modulo 11 must equal digit 10 (Teleplan example `9012372173`). A result of 10 or 11 equals no digit, so such a
 * number is refused.
 *
 * @see https://www2.gov.bc.ca/assets/gov/health/practitioner-pro/medical-services-plan/teleplan-ch1.pdf BC Ministry of
 *      Health, "Teleplan4 Web Record Specifications" v4.8, chapter 1, §1.14.1-1.14.2
 * @see https://files.ontario.ca/moh_1/moh-province-and-territory-codes-2022-09-en-2023-05-15.pdf Ontario Ministry of
 *      Health, "Province and Territory Codes" (September 2022)
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class BcHealthCardNumber extends AbstractHealthCardNumber
{
    public const int MAX_LENGTH = 10;

    private const array WEIGHTS = [0, 2, 4, 8, 5, 10, 9, 7, 3];

    private const int MODULUS = 11;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::BC;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('9[0-9]{9}');
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        $remainder = Checksum::weightedMod(\substr($normalized, 0, 9), self::WEIGHTS, self::MODULUS);
        if (self::MODULUS - $remainder !== (int) $normalized[9]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', $document));
        }

        return $normalized;
    }
}
