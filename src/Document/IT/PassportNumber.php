<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\IT;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Serial number of the ordinary and the temporary passport: "una sequenza alfanumerica di due lettere indicanti
 * tipologia e serie del passaporto, seguite da sette numeri arabi" (`YA1234567`). The decrees give no meaning to each
 * letter, so the letters are not exposed. The number has no check digit of its own; the check digit is an MRZ field.
 * `masked()` keeps the last four characters (no official masking rule): `*****4567`.
 *
 * @see https://www.gazzettaufficiale.it/eli/gu/2010/05/19/115/sg/html Ministero degli affari esteri, DM 23 marzo
 *      2010, n. 303/13 (ordinary passport) and n. 303/14 (temporary passport), art. 1 A) "Numero di serie del
 *      passaporto"
 * @see docs/sources/IT.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Italian passport number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 9, 9)->parse($value, self::DOCUMENT);
        if (\strspn($number, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') !== 2 || \strspn($number, '0123456789', 2) !== 7) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
