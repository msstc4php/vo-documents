<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Saskatchewan: "Your health card number is nine digits" (eHealth Saskatchewan); "9 numerics-individual
 * registration" (Ontario table). Format only: no check digit is published.
 *
 * @see https://files.ontario.ca/moh_1/moh-province-and-territory-codes-2022-09-en-2023-05-15.pdf Ontario Ministry of
 *      Health, "Province and Territory Codes" (September 2022)
 * @see https://www.ehealthsask.ca/residents/mysaskhealthrecord/account-support eHealth Saskatchewan,
 *      MySaskHealthRecord "Support for your account"
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class SkHealthCardNumber extends AbstractHealthCardNumber
{
    public const int MAX_LENGTH = 9;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::SK;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{9}');
    }
}
