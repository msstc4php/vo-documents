<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Ontario: "A unique 15 character identifier assigned to each driver in Ontario", "Free form 15-character
 * alphanumeric" (MTO glossary, sample `X0915-26435-95718`); "Display as is", so there is no `formatted()`. Format
 * only: MTO defines no encoding in text. The Photo Card's Driver Identification Number is a different number.
 *
 * @see https://www.cdr.mto.gov.on.ca/assets/files/glossary/Business%20Glossary.pdf MTO Carrier and Driver Records,
 *      "Business Glossary"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
final class OnDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    public const int MAX_LENGTH = 15;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::ON;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9A-Z]{15}');
    }
}
