<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Passport number: nine characters, two digits, two letters and five digits (`12AB12345`). The format is ANTS's
 * answer about the number of the previous passport to enter in a renewal pre-application (no decree states it,
 * docs/sources/FR.md). The number has no check
 * digit of its own; the check digit is an MRZ field. `masked()` keeps the last four characters (no official masking
 * rule): `*****2345`.
 *
 * @see https://www.plus.transformation.gouv.fr/experiences/4124633_passeport France Titres-ANTS: "9 caractères -
 *      (2 chiffres, 2 lettres, 5 chiffres)"
 * @see docs/sources/FR.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'French passport number';

    private const string DIGITS = '0123456789';

    private const string LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 9, 9)->parse($value, self::DOCUMENT);
        if (
            \strspn($number, self::DIGITS) !== 2
            || \strspn($number, self::DIGITS, 4) !== 5
            || \strspn($number, self::LETTERS, 2) !== 2
        ) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
