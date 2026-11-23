<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Nova Scotia: the "Master Number" on the driver's licence and the NS Identification Card, "the first 5 letters of
 * your last name [if your last name has fewer than 5 letters, the difference is made up with spaces]; plus the day /
 * month / year of your birth; and plus 3 computer-assigned numbers", e.g. `FINCH--220867001`. The padding spaces and
 * hyphens are separators, so the stored form is one to five letters and nine digits (`FOY  220867001` is
 * `FOY220867001`).
 *
 * The day (01-31) and month (01-12) are checked as calendar fields, a rule derived from the encoding text rather than
 * quoted as a validation rule. The year has two digits and no century, so the class has no birth accessors.
 *
 * @see https://novascotia.ca/sns/paal/rmv/paal275.asp Service Nova Scotia, Permits Directory, "ID Cards (Photo)"
 * @see docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
final class NsDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    private const int DIGITS = 9;

    private const string DAY_MONTH = '/^(?:0[1-9]|[12][0-9]|3[01])(?:0[1-9]|1[0-2])/';

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::NS;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes(
            '[A-Z][0-9]{9}',
            '[A-Z]{2}[0-9]{9}',
            '[A-Z]{3}[0-9]{9}',
            '[A-Z]{4}[0-9]{9}',
            '[A-Z]{5}[0-9]{9}',
        );
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        if (\preg_match(self::DAY_MONTH, \substr($normalized, -self::DIGITS)) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', $document));
        }

        return $normalized;
    }
}
