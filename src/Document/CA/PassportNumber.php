<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Canadian passport number: since 10 May 2023 a letter, six digits and two letters (`A123456BC`); before, two letters
 * and six digits (`AB123456`), still on passports issued before that date. Format only: no check digit or letter set
 * is published (the MRZ check digit is separate). `masked()` keeps the last four characters, three for the
 * eight-character form (no official masking rule): `*****56BC`, `*****456`.
 *
 * @see https://www.ircc.canada.ca/english/helpcentre/answer.asp?qnum=1446 IRCC Help Centre, "Where is my status in
 *      Canada document number?"
 * @see https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-passports/new-passport-features.html
 *      IRCC, "Features of Canada's new passport"
 * @see docs/sources/CA.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Canadian passport number';

    #[Override]
    public function masked(): string
    {
        $value = Text::nonEmpty($this->value);

        return Mask::keepLastCharacters($value, \strlen($value) <= 8 ? 3 : 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 8, 9)->parse($value, self::DOCUMENT);
        if (\preg_match('/^(?:[A-Z]{2}[0-9]{6}|[A-Z][0-9]{6}[A-Z]{2})$/', $number) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
