<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Tax file number: 9 digits. Format only: the check digit is not verified because no official algorithm is
 * published (the ATO gives its TFN algorithm to registered software developers only, AU.md). The 8-digit TFNs once
 * issued to non-individuals are not accepted.
 *
 * `masked()` (no official rule) keeps the last three digits, not the package's usual four: AU numbers are short
 * (at most 10 characters), so four would show almost half of them. `*** *** 782`.
 *
 * @see https://softwaredevelopers.ato.gov.au/obtainTFNalgorithm ATO, "How to obtain the tax file number algorithm"
 * @see docs/sources/AU.md "Tax file number"
 */
final class TaxFileNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Australian tax file number';

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return \sprintf('%s %s %s', \substr($this->value, 0, 3), \substr($this->value, 3, 3), \substr($this->value, 6));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 3);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 9, self::DOCUMENT);
    }
}
