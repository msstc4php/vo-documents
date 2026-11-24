<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Social Insurance Number, `123 456 789`: nine digits. `from()` checks the format only: the CRA tells tax preparers
 * to report a SIN that fails its check "even if it is not a valid number", and its own sample SINs `123 456 789` and
 * `111 111 111` fail it; {@see hasValidCheckDigit()} reports the CRA check. No first digit is refused: no ESDC text
 * marks a leading digit unassigned. ESDC states that SINs "beginning with a "9" are issued to temporary workers who are neither Canadian citizens nor
 * permanent residents", which {@see isTemporaryResident()} reads. `masked()` keeps the last four digits (no official
 * masking rule): `*** **6 789`.
 *
 * @see https://bac-lac.wayback.archive-it.org/web/20061210041004/http:/www.cra-arc.gc.ca/eservices/magmedia/sin-e.pdf
 *      CRA, "Validating a Social Insurance Number -- Example" (Library and Archives Canada web archive)
 * @see https://www.canada.ca/en/employment-social-development/services/sin/temporary-residents.html ESDC, "SIN for
 *      temporary residents in Canada"
 * @see https://www.canada.ca/en/employment-social-development/programs/ei/ei-list/ei-employers-sin.html ESDC,
 *      "Employer information - Social Insurance Number (SIN)"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CA.md "Social Insurance Number"
 */
final class SocialInsuranceNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 9;

    private const string DOCUMENT = 'Canadian Social Insurance Number';

    /**
     * Whether the SIN begins with 9, the series ESDC issues to temporary residents (it expires with the immigration
     * document; the expiry date is not part of the number).
     */
    public function isTemporaryResident(): bool
    {
        return $this->value[0] === '9';
    }

    /**
     * Whether the ninth digit is the check digit of the CRA's "Validating a Social Insurance Number" (the Luhn sum;
     * `999 999 998` is the CRA's worked example). Report only: a SIN that fails is still accepted.
     */
    public function hasValidCheckDigit(): bool
    {
        return Checksum::luhn($this->value);
    }

    /**
     * @return non-empty-string e.g. "123 456 789"
     */
    public function formatted(): string
    {
        return \sprintf('%s %s %s', \substr($this->value, 0, 3), \substr($this->value, 3, 3), \substr($this->value, 6));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 9, self::DOCUMENT);
    }
}
