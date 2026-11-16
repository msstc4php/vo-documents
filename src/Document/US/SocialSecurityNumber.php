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
 * Social Security number, `123-45-6789`: area (3 digits), group (2) and serial (4). Refused only as the SSA states it:
 * area 000, 666 and 900-999 (so an ITIN, always `9…`, is never an SSN), group 00 and serial 0000. There is no check
 * digit; the area no longer has a geographic meaning (randomisation of 25 June 2011). `masked()` is the IRS truncated
 * form (26 CFR 301.6109-4: the first five digits replaced, "XXX-XX-1234"): `***-**-6789`.
 *
 * @see https://www.ssa.gov/employer/randomizationfaqs.html SSA, Social Security Number Randomization, FAQ
 * @see https://www.irs.gov/irm/part3/irm_03-013-005 IRM 3.13.5.21 "Social Security Number (SSN) Format"
 * @see https://www.ecfr.gov/current/title-26/section-301.6109-4 26 CFR 301.6109-4 (TTIN)
 * @see docs/sources/US.md "Social Security number"
 */
final class SocialSecurityNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'American Social Security number';

    /**
     * @return non-empty-string e.g. "123-45-6789"
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
        $area = \substr($number, 0, 3);
        if ($area === '000' || $area === '666' || $number[0] === '9') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (area number).', self::DOCUMENT));
        }

        if (\substr($number, 3, 2) === '00') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (group number).', self::DOCUMENT));
        }

        if (\substr($number, 5) === '0000') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (serial number).', self::DOCUMENT));
        }

        return $number;
    }
}
