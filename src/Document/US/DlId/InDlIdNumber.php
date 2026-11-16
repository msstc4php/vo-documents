<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Indiana: "your insured 10 digit Indiana DLN". The source states no ID-card numbering, so the class covers the driver
 * licence only.
 *
 * @see https://www.in.gov/bmv/resources/insurance-agents/electronic-insurance-forms-submission-faqs Indiana BMV,
 *      "Electronic Insurance Forms Submission FAQs"
 * @see docs/sources/US.md "DL/ID numbers"
 */
final class InDlIdNumber extends AbstractDlIdNumber
{
    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::In;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{10}');
    }
}
