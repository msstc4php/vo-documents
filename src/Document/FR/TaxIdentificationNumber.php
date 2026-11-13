<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Numéro fiscal de référence (numéro SPI): thirteen digits, the first one 0, 1, 2 or 3, written
 * `99 99 999 999 999`. Format only: no check digit or encoded data is published. `masked()` keeps the last four
 * digits (no official masking rule): `** ** *** **0 053`.
 *
 * @see https://www.oecd.org/content/dam/oecd/en/topics/policy-issue-focus/aeoi/france-tin.pdf France, Information on
 *      Tax Identification Numbers, Sections I and II
 * @see docs/sources/FR.md "Numéro fiscal"
 */
final class TaxIdentificationNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'French tax identification number';

    /**
     * @return non-empty-string e.g. "30 23 217 600 053"
     */
    public function formatted(): string
    {
        return \sprintf(
            '%s %s %s %s %s',
            \substr($this->value, 0, 2),
            \substr($this->value, 2, 2),
            \substr($this->value, 4, 3),
            \substr($this->value, 7, 3),
            \substr($this->value, 10),
        );
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = Digits::exactly($value, 13, self::DOCUMENT);
        if ($number[0] > '3') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid first digit).', self::DOCUMENT));
        }

        return $number;
    }
}
