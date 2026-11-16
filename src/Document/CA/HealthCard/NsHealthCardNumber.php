<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Nova Scotia: MSI, "10 numerics-individual registration (effective January 1, 1994)" (Ontario table); "the
 * insured person's ten-digit health insurance number" (Alberta Health). Format only. The family-based 11-digit numbers
 * of before 1994-01-01 are not accepted.
 *
 * @see https://files.ontario.ca/moh_1/moh-province-and-territory-codes-2022-09-en-2023-05-15.pdf Ontario Ministry of
 *      Health, "Province and Territory Codes" (September 2022)
 * @see https://open.alberta.ca/dataset/d79fa4a5-f134-464f-8f49-6787c6408a75/resource/70f56882-e5df-4a80-9035-1d90108c8978/download/hlth-physicians-resource-guide-2024.pdf
 *      Alberta Health, "Physician's Resource Guide 2024", Appendix D (read through the Internet Archive copy of
 *      2026-08-30 UTC)
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class NsHealthCardNumber extends AbstractHealthCardNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::Ns;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{10}');
    }
}
