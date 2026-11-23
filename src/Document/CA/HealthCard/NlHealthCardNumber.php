<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Newfoundland and Labrador: MCP, "12 numerics-individual registration" (Ontario table). Format only: no check
 * digit is published.
 *
 * @see https://files.ontario.ca/moh_1/moh-province-and-territory-codes-2022-09-en-2023-05-15.pdf Ontario Ministry of
 *      Health, "Province and Territory Codes" (September 2022)
 * @see https://www.gov.nl.ca/hcs/files/mcp-providers-pim-residents-of-other-provinces.pdf NL Department of Health
 *      & Community Services, MCP Physician Information Manual, "Residents of Other Provinces"
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class NlHealthCardNumber extends AbstractHealthCardNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NL;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{12}');
    }
}
