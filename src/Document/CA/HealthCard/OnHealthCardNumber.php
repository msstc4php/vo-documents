<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Ontario: the health number, `pattern="[1-9]\d{9}"`, "a ten-digit number that appears on the face of every health
 * card", verified by the MOD 10 check digit (ON-IHCS example `9876543217`; HCV response `FAILED_MOD10`). Two cards of
 * one person have the same number and differ in the {@see OnHealthCardVersionCode}.
 *
 * The printed card adds the version code, "one letter or two letters" (`9876543217 YM`): {@see fromCard()} splits it.
 * {@see from()} refuses a number followed by letters with "(version code: use fromCard())" instead of dropping them,
 * so a code is never lost without the caller choosing to.
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
    public const int MAX_LENGTH = 10;

    private const int DIGITS = 10;

    private const string DOCUMENT = 'CA-ON health card number';

    /**
     * Splits the printed card, `9876543217 YM`, into the number and the version code (null when none is printed).
     *
     * @throws InvalidDocumentNumberException as {@see from()} for the number, or "(invalid character)" for a version
     *                                        code of other than one or two letters
     */
    public static function fromCard(string $printed): OnHealthCard
    {
        $normalized = self::shapes()->parse($printed, self::DOCUMENT);
        $versionCode = \substr($normalized, self::DIGITS);

        return new OnHealthCard(
            self::from(\substr($normalized, 0, self::DIGITS)),
            $versionCode === '' ? null : OnHealthCardVersionCode::from($versionCode),
        );
    }

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::ON;
    }

    /**
     * The number with or without the version code, so that {@see fromCard()} and a refusal of the printed form in
     * {@see from()} share one shape check.
     */
    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[1-9][0-9]{9}', '[1-9][0-9]{9}[A-Z]', '[1-9][0-9]{9}[A-Z]{2}');
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        if (\strlen($normalized) !== self::DIGITS) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (version code: use fromCard()).', $document));
        }

        if (!Checksum::luhn($normalized)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', $document));
        }

        return $normalized;
    }
}
