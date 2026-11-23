<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Manitoba: the Personal Health Identification Number (PHIN), "a nine-digit lifetime identification number for
 * each Manitoban" (Manitoba Health); "9 numerics – individual registration (effective April 1, 2005)" (Ontario
 * table). Format only. The six-character family registration number on the same card is not covered.
 *
 * @see https://files.ontario.ca/moh_1/moh-province-and-territory-codes-2022-09-en-2023-05-15.pdf Ontario Ministry of
 *      Health, "Province and Territory Codes" (September 2022)
 * @see https://www.gov.mb.ca/health/mhsip/health-card-information.html Manitoba Health, Seniors and Long-Term Care,
 *      "Your Manitoba Health Card (Registration Certificate)"
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class MbHealthCardNumber extends AbstractHealthCardNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::MB;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}');
    }
}
