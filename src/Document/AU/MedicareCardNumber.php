<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Medicare card number: 10 digits, printed `3189 97702 1`: an 8-digit card number, a check digit and the issue number
 * (Australian Digital Health Agency). Format only: the check digit and any restriction of the first digit are not
 * verified because no official algorithm or rule is published (AU.md). The 11-digit individual number (with the individual reference number) is not this value object.
 *
 * `masked()` (no official rule) keeps the last three digits, not the package's usual four: AU numbers are short
 * (at most 10 characters), so four would show almost half of them. `**** ***02 1`.
 *
 * @see https://implementer.digitalhealth.gov.au/namespaces/id/medicare-number/index.html Australian Digital Health
 *      Agency, namespace http://ns.electronichealth.net.au/id/medicare-number
 * @see docs/sources/AU.md "Medicare card number"
 */
final class MedicareCardNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Medicare card number';

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return \sprintf('%s %s %s', \substr($this->value, 0, 4), \substr($this->value, 4, 5), \substr($this->value, 9));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 3);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 10, self::DOCUMENT);
    }
}
