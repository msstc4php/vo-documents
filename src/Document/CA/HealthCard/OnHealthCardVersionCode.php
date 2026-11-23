<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * The version code of an Ontario health card: "When present on the card, a version code is one letter or two
 * letters" (ON-HCV, field `versionCode`), "an alphabetic identifier that along with the health number uniquely
 * identifies a health card version". Read after the separators are removed and ASCII letters upper-cased. A new card
 * of the same person gets a new code and keeps the {@see OnHealthCardNumber}.
 *
 * `masked()` hides every letter (`**`): with the number the code identifies a card, and one of two letters is half
 * of it. Whether a code is the current card's needs the ministry's validation service and is not modelled.
 *
 * @see https://www.ontario.ca/files/2025-10/moh-ohip-technical-specification-health-card-validation-service-via-electronic-business-services-en-2025-10-08.pdf
 *      Ontario Ministry of Health, "Technical Specification for Health Card Validation (HCV) Service via EBS" v4.6
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class OnHealthCardVersionCode extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 2;

    private const string DOCUMENT = 'CA-ON health card version code';

    #[Override]
    public function masked(): string
    {
        return Text::nonEmpty(\str_repeat('*', \strlen($this->value)));
    }

    /**
     * @return non-empty-string
     *
     * @throws InvalidDocumentNumberException "(wrong length)" for other than one or two characters, "(invalid
     *                                        character)" for anything but a letter
     */
    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[A-Z]', '[A-Z]{2}')->parse($value, self::DOCUMENT);
    }
}
