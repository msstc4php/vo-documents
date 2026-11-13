<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\TR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Turkish passport number: one letter and eight digits (`U00000261`, printed `U 00000261` on the 2010 model and
 * `U20041774` on the 2018 model). No Turkish act publishes the layout; it is the composition "A NNNNNNNN" of the EU
 * PRADO description, which both specimens follow. No check digit (the MRZ check digit is a separate field).
 * `masked()` keeps the last four characters (no official masking rule): `*****0261`.
 *
 * @see https://www.consilium.europa.eu/prado/en/TUR-AO-02001/index.html PRADO TUR-AO-02001, biodata page "Numbering"
 *      (read through the Internet Archive copy of 2026-08-18)
 * @see https://www.consilium.europa.eu/prado/en/TUR-AO-03001/index.html PRADO TUR-AO-03001, specimen
 * @see docs/sources/TR.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Turkish passport number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 9, 9)->parse($value, self::DOCUMENT);
        // Unanchored: NumberFormat has fixed the length at nine.
        if (\preg_match('/[A-Z][0-9]{8}/', $number) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
