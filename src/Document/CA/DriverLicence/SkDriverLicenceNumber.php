<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * Saskatchewan: the SGI "customer number" ("Customer number is eight digits"), printed as "LICENCE NUMBER (PIC)", on
 * the driver's licence and the non-driver photo ID alike. "Temporary cards may not display the leading 0s … please add
 * zeros to the front of the number", so `890562` and `00890562` are one number, stored as eight digits. The per-card
 * validation number is not covered.
 *
 * @see https://www.saskatchewan.ca/government/doing-business-with-government/saskatchewan-account-help/identity-validation-and-privacy
 *      Government of Saskatchewan, "Identity Validation and Privacy"
 * @see https://www.saskatchewan.ca/-/media/news-archive/2010/november/18/sgi-unveils-new-onepart-drivers-licence/onepart-drivers-licence-backgrounder.pdf
 *      SGI, "Saskatchewan's One-part Driver's Licence" backgrounder
 * @see docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
final class SkDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    private const int LENGTH = 8;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::SK;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes(
            '[0-9]',
            '[0-9]{2}',
            '[0-9]{3}',
            '[0-9]{4}',
            '[0-9]{5}',
            '[0-9]{6}',
            '[0-9]{7}',
            '[0-9]{8}',
        );
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        return Text::nonEmpty(\str_pad($normalized, self::LENGTH, '0', \STR_PAD_LEFT));
    }
}
