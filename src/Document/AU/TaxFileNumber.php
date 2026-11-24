<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Tax file number: 8 or 9 digits (ANAO: "a eight or nine digit number"; 8 digits accepted since 1.2), kept as written:
 * `12345678` is not padded, so it is not equal to `012345678`. Format only: the check digit is not verified because
 * no official algorithm is published (the ATO gives its TFN algorithm on request, under a confidentiality agreement,
 * AU.md).
 *
 * `formatted()` groups three digits from the right (`123 456 782`, `12 345 678`; no official display form for 8
 * digits). `masked()` (no official rule) keeps the last three digits of 9, not the package's usual four: AU numbers
 * are short (at most 10 characters), so four would show almost half of them. Of 8 digits it keeps two, so it hides at
 * least the share it hides of 9: `*** *** 782`, `** *** *78`.
 *
 * @see https://www.anao.gov.au/sites/default/files/anao_report_1998-99_37.pdf ANAO Audit Report No. 37 1998-99, § 1.5
 * @see https://softwaredevelopers.ato.gov.au/obtainTFNalgorithm ATO, "How to obtain the tax file number algorithm"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/AU.md "Tax file number"
 */
final class TaxFileNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 9;

    private const string DOCUMENT = 'Australian tax file number';

    /**
     * @return non-empty-string e.g. "123 456 782", "12 345 678"
     */
    public function formatted(): string
    {
        return \sprintf(
            '%s %s %s',
            \substr($this->value, 0, -6),
            \substr($this->value, -6, 3),
            \substr($this->value, -3),
        );
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), \strlen($this->value) === 8 ? 2 : 3);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::strip($value);

        return Digits::exactlyStripped($digits, \mb_strlen($digits, 'UTF-8') === 8 ? 8 : 9, self::DOCUMENT);
    }
}
