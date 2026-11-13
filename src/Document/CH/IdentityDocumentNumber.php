<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CH;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Ausweisnummer of a Swiss passport or identity card: eight letters or digits, randomly assigned; the letters `I` and
 * `O` are never used. No position rule is published, so none is checked; the specimen is `S1A00A00`. The number has
 * no check digit of its own (the MRZ check digit is separate). `masked()` keeps the last three characters (no
 * official masking rule): `*****A00`.
 *
 * @see https://www.fedlex.admin.ch/eli/cc/2010/96/de SR 143.111, Art. 16
 * @see http://web.archive.org/web/20260214105901/https://www.fedpol.admin.ch/fedpol/de/home/pass---identitaetskarte/pass.html
 *      fedpol, "Pass", box "Ausweisnummer" (Internet Archive copy of 2026-02-14)
 * @see https://media.bs.ch/original_file/f9c82519e56d4de65ec334e1f12a254364907d80/pw-faq-neuer-schweizer-pass-2024-06.pdf
 *      Kanton Basel-Stadt, Passamt, FAQ neuer Schweizer Pass ("Wo finde ich die Ausweisnummer?")
 * @see docs/sources/CH.md "Passport and identity card number"
 */
final class IdentityDocumentNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Swiss passport or identity card number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 3);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberFormat('A-HJ-NP-Z0-9', 8, 8)->parse($value, self::DOCUMENT);
    }
}
