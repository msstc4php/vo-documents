<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\JP;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * 個人番号 (My Number): "11桁の番号と１桁の検査用数字" (12 digits). The check digit is 11 - (Σ Pn×Qn mod 11), with 0
 * when the remainder is 0 or 1; Pn is the nth digit from the right of the 11 leading digits, Qn is n+1 for n = 1..6
 * and n-5 for n = 7..11 (総務省令 Art. 5). No display form is published; `masked()` keeps the last four digits (no
 * official masking rule). The corporate number (法人番号, 13 digits) is a different number and is not covered.
 *
 * @see https://laws.e-gov.go.jp/law/426M60000008085 行政手続における特定の個人を識別するための番号の利用等に関する法律に規定する
 *      個人番号、個人番号カード、特定個人情報の提供等に関する命令, Art. 5
 * @see docs/sources/JP.md "My Number"
 */
final class MyNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'My Number';

    /** Q(n) for the 11 leading digits, read from the left (n = 11 down to 1). */
    private const array WEIGHTS = [6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2];

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 12, self::DOCUMENT);

        $remainder = Checksum::weightedMod(\substr($digits, 0, 11), self::WEIGHTS, 11);
        $check = $remainder <= 1 ? 0 : 11 - $remainder;
        if ((int) $digits[11] !== $check) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $digits;
    }
}
