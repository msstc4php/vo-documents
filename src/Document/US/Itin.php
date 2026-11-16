<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * IRS Individual Taxpayer Identification Number, `9NN-NN-NNNN`: nine digits, the first always 9, the fourth and fifth
 * in 50-65, 70-88, 90-92 or 94-99 as the IRS assigns them (89 and 93 are "reserved for other programs": 93 is the
 * ATIN). There is no check digit, and it never passes as a {@see SocialSecurityNumber} (area 900-999). `masked()` is
 * the IRS truncated form (26 CFR 301.6109-4): `***-**-1234`.
 *
 * @see https://www.irs.gov/irm/part3/irm_03-013-005 IRM 3.13.5.22 "Individual Taxpayer Identification Number (ITIN)
 *      Format"
 * @see https://www.ecfr.gov/current/title-26/section-301.6109-4 26 CFR 301.6109-4 (TTIN)
 * @see docs/sources/US.md "ITIN"
 */
final class Itin extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'American individual taxpayer identification number';

    /** The IRS ranges of the fourth and fifth digits, bounds included; two-digit strings compare in order. */
    private const array MIDDLE_RANGES = [['50', '65'], ['70', '88'], ['90', '92'], ['94', '99']];

    /**
     * @return non-empty-string e.g. "912-70-1234", the SSN layout the IRS displays
     */
    public function formatted(): string
    {
        return \sprintf('%s-%s-%s', \substr($this->value, 0, 3), \substr($this->value, 3, 2), \substr($this->value, 5));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = Digits::exactly($value, 9, self::DOCUMENT);
        if ($number[0] !== '9') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (first digit).', self::DOCUMENT));
        }

        $middle = \substr($number, 3, 2);
        if (!\array_any(
            self::MIDDLE_RANGES,
            static fn (array $range): bool => $middle >= $range[0] && $middle <= $range[1],
        )) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (fourth and fifth digits).', self::DOCUMENT));
        }

        return $number;
    }
}
