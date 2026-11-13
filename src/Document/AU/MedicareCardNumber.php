<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Medicare card number: 10 digits, printed `3189 97702 1`: an 8-digit card number, a check digit and the issue number
 * (Australian Digital Health Agency). The first digit 2-6 and the check digit (the card number's digits weighted
 * 1, 3, 7, 9, 1, 3, 7, 9, summed, mod 10) have no public official text; the algorithm is the one used in practice
 * (AU.md). The 11-digit individual number (with the individual reference number) is not this value object.
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

    private const array WEIGHTS = [1, 3, 7, 9, 1, 3, 7, 9];

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
        $digits = Digits::exactly($value, 10, self::DOCUMENT);

        if ($digits[0] < '2' || $digits[0] > '6') {
            throw new InvalidDocumentNumberException('Invalid ' . self::DOCUMENT . ' (first digit out of range).');
        }

        if (Checksum::weightedMod(\substr($digits, 0, 8), self::WEIGHTS, 10) !== (int) $digits[8]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $digits;
    }
}
