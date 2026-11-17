<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\JP;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * 在留カード番号: "12桁（英字２桁ｰ数字８桁ｰ英字２桁）". Format only: the Immigration Services Agency publishes neither
 * the meaning of the letters nor a check rule. The hyphens of the quoted form are separators and are not stored.
 * `masked()` keeps the last four characters (no official masking rule).
 *
 * @see https://jsite.mhlw.go.jp/shimane-roudoukyoku/content/contents/001640228.pdf 厚生労働省 島根労働局
 * @see docs/sources/JP.md "Residence card number"
 */
final class ResidenceCardNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Japanese residence card number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[A-Z]{2}[0-9]{8}[A-Z]{2}')->parse($value, self::DOCUMENT);
    }
}
