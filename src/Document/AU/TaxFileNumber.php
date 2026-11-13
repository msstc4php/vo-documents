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
 * Tax file number: 9 digits whose sum weighted 1, 4, 3, 7, 5, 8, 6, 9, 10 is a multiple of 11. No public official
 * text; algorithm as used in practice: the ATO gives its TFN algorithm to registered software developers only
 * (AU.md). The 8-digit TFNs once issued to non-individuals are not accepted. `masked()` keeps the last three digits
 * (no official rule): `*** *** 782`.
 *
 * @see https://softwaredevelopers.ato.gov.au/obtainTFNalgorithm ATO, "How to obtain the tax file number algorithm"
 * @see docs/sources/AU.md "Tax file number"
 */
final class TaxFileNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Australian tax file number';

    private const array WEIGHTS = [1, 4, 3, 7, 5, 8, 6, 9, 10];

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
        $digits = Digits::exactly($value, 9, self::DOCUMENT);

        if (Checksum::weightedMod($digits, self::WEIGHTS, 11) !== 0) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $digits;
    }
}
