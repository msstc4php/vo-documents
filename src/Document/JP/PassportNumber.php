<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\JP;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Japanese passport number: "英字2桁と数字7桁" (MOFA's sample is `AB1234567`). Format only: which letters are used for
 * which passport is not published by the issuer and is not checked, and no check rule is published. `masked()` keeps
 * the last four characters (no official masking rule).
 *
 * @see https://web.archive.org/web/2025/https://www.mofa.go.jp/mofaj/files/100130952.pdf 外務省, 旅券への別名併記の記載方法変更
 * @see https://www.pref.kanagawa.jp/docs/h6d/passport/aboutpass.html 神奈川県, パスポートの読みかた等
 * @see docs/sources/JP.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Japanese passport number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[A-Z]{2}[0-9]{7}')->parse($value, self::DOCUMENT);
    }
}
