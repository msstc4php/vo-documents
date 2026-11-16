<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * US passport card number: the letter `C` and eight digits. Format only: no check digit is published. `masked()` keeps
 * the last four characters (no official masking rule): `*****5678`.
 *
 * @see https://everify.uscis.gov/ica-docs/cases/appendix/field-definitions/uspassport-and-uspassport-card USCIS,
 *      E-Verify, "U.S. Passport and U.S. Passport Card"
 * @see docs/sources/US.md "Passport card number"
 */
final class PassportCardNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'American passport card number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 9, 9)->parse($value, self::DOCUMENT);
        // Unanchored: the length is fixed at nine above, so anchors would be equivalent mutants.
        if (\preg_match('/C[0-9]{8}/', $number) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
