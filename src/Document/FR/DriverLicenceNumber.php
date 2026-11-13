<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Driving licence number (numéro de dossier, NEPH) of a first licence obtained after 1 June 1975: twelve digits.
 * Format only: no check digit or field layout is published. Licences obtained before 1 June 1975 (1 to 15 characters
 * and further fields) are not covered. `masked()` keeps the last four digits (no official masking rule):
 * `********2345`.
 *
 * @see https://www.plus.transformation.gouv.fr/experiences/4598427_permis-de-conduire France Titres-ANTS: "Votre
 *      numéro de permis de conduire comporte 12 chiffres"
 * @see docs/sources/FR.md "Driving licence number"
 */
final class DriverLicenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'French driving licence number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 12, self::DOCUMENT);
    }
}
