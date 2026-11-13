<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * British passport number: nine digits on every design from 1988 to the series D e-Passport of 2025. Format only: no
 * check digit is published (the MRZ check digit is separate, and the thin-film serial number of three letters and four
 * digits is a different number). `masked()` keeps the last four digits (no official masking rule): `*****6789`.
 *
 * @see https://assets.publishing.service.gov.uk/media/6979d8b4128d9c1d09a98c73/2025_BasicPassportChecks_1.1.pdf
 *      HM Passport Office, Basic Passport Checks 1.1, "Numbering"
 * @see docs/sources/GB.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'British passport number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 9, self::DOCUMENT);
    }
}
