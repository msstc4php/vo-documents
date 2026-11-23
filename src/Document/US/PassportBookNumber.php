<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * US passport book number: a letter and eight digits on the next generation book (2021 on), nine digits on most older
 * books still in circulation, a letter and seven digits on a few. Format only: no check digit or letter set is
 * published, so any letter is accepted (a passport card number, `C` + eight digits, fits the book shape too).
 * `masked()` keeps the last four characters, three for an eight-character number (no official masking rule):
 * `*****5678`, `*****567`.
 *
 * @see https://travel.state.gov/content/travel/en/passports/passport-help/next-generation-passport.html U.S.
 *      Department of State, "Information about the Next Generation U.S. Passport"
 * @see https://everify.uscis.gov/ica-docs/cases/appendix/field-definitions/uspassport-and-uspassport-card USCIS,
 *      E-Verify, "U.S. Passport and U.S. Passport Card"
 * @see docs/sources/US.md "Passport book number"
 */
final class PassportBookNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 9;

    private const string DOCUMENT = 'American passport book number';

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
        if (\preg_match('/^(?:[0-9]{9}|[A-Z][0-9]{7,8})$/', $number) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
