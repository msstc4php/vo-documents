<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\JP;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * 基礎年金番号: "4桁と6桁の数字を組み合わせた10桁の数字". Format only: no check rule and no meaning of the two parts is
 * published, so there is no accessor and no display form. `masked()` keeps the last four digits (no official masking
 * rule).
 *
 * @see https://www.nenkin.go.jp/section/faq/seidozenpan/kisoban/kisonenkinbango/20140807-02.html 日本年金機構
 * @see docs/sources/JP.md "Basic Pension Number"
 */
final class BasicPensionNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 10;

    private const string DOCUMENT = 'Basic Pension Number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 10, self::DOCUMENT);
    }
}
