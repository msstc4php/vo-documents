<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * Ontario: the health number, `pattern="[1-9]\d{9}"`, "a ten-digit number that appears on the face of every health
 * card", verified by the MOD 10 check digit (ON-IHCS example `9876543217`; HCV response `FAILED_MOD10`).
 *
 * The input may end with the card's version code, "one letter or two letters" (`9876543217 YM`): it is kept apart
 * from the value, which is the ten digits, so two cards of one person have the same value and equal each other, and
 * the string and JSON forms drop the code. Photo cards always carry two letters, but a red-and-white card may have
 * none. Whether a version code is the current card's needs the ministry's validation service and is not modelled.
 *
 * @see https://www.ontario.ca/files/2025-10/moh-ohip-technical-specification-health-card-validation-service-via-electronic-business-services-en-2025-10-08.pdf
 *      Ontario Ministry of Health, "Technical Specification for Health Card Validation (HCV) Service via EBS" v4.6
 * @see https://files.ontario.ca/moh-tech-spec-health-care-systems-manual-en-2023-06-12.pdf Ontario Ministry of Health,
 *      "Interface to Health Care Systems Technical Specifications" v6.2, §4.14 "MOD 10 Check Digit"
 * @see https://www.ontario.ca/files/2024-10/moh-health-card-validation-reference-manual-en-2024-10-10.pdf Ontario
 *      Ministry of Health, "Health Card Validation Reference Manual", "Version Codes"
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class OnHealthCardNumber extends AbstractHealthCardNumber
{
    private const int DIGITS = 10;

    /** @var non-empty-string|null */
    private ?string $versionCode = null;

    /**
     * @return non-empty-string|null one or two upper-case letters, null when the input had none
     */
    public function versionCode(): ?string
    {
        return $this->versionCode;
    }

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::On;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[1-9][0-9]{9}', '[1-9][0-9]{9}[A-Z]', '[1-9][0-9]{9}[A-Z]{2}');
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        $healthNumber = Text::nonEmpty(\substr($normalized, 0, self::DIGITS));
        if (!Checksum::luhn($healthNumber)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', $document));
        }

        $versionCode = \substr($normalized, self::DIGITS);
        $this->versionCode = $versionCode === '' ? null : $versionCode;

        return $healthNumber;
    }
}
