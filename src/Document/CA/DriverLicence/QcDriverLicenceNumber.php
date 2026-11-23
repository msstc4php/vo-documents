<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use Override;

/**
 * Québec: the SAAQ number "contient 13 caractères et commence par une lettre (format : A0000-000000-00)";
 * `formatted()` gives that form, `L1531-171274-08` (SAAQ card guide, field 4d). Format only: SAAQ defines no encoding
 * of the number in text.
 *
 * @see https://saaq.gouv.qc.ca/blob/saaq/documents/promotion-services/saaqclic-commercants/aide-memoire-saaqclic-connexion-administrateur-commerce-agree.pdf
 *      SAAQ, "Aide-mémoire — Première connexion à SAAQclic Commerçants" (read through the Internet Archive copy of
 *      2024-05-07 UTC)
 * @see https://saaq.gouv.qc.ca/blob/saaq/documents/publications/presentation-quebec-driver-licences.pdf SAAQ,
 *      "Presentation of Québec driver's licences" (read through the Internet Archive copy of 2026-07-18 UTC)
 * @see docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
final class QcDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return self::layout(Text::nonEmpty($this->value));
    }

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::QC;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{12}');
    }

    #[Override]
    protected static function layout(string $compact): string
    {
        return \sprintf('%s-%s-%s', \substr($compact, 0, 5), \substr($compact, 5, 6), \substr($compact, 11));
    }
}
