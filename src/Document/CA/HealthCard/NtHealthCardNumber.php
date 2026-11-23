<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Northwest Territories: the Health Service Number, "8 characters-individual registration. One alpha (D, N, H, M or
 * T and 7 numerics)" (Ontario table, letter H added by OHIP INFOBulletin 220902); the letter set is closed and
 * enforced. Format only: no check digit is published.
 *
 * @see https://files.ontario.ca/moh_1/moh-province-and-territory-codes-2022-09-en-2023-05-15.pdf Ontario Ministry of
 *      Health, "Province and Territory Codes" (September 2022)
 * @see https://www.ontario.ca/document/ohip-infobulletins-2022/bulletin-220902-northwest-territories-health-service-number
 *      Ontario Ministry of Health, OHIP INFOBulletin 220902
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class NtHealthCardNumber extends AbstractHealthCardNumber
{
    public const int MAX_LENGTH = 8;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NT;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[DNHMT][0-9]{7}');
    }
}
