<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CN;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Number of the 往来台湾通行证 a mainland resident travels to Taiwan with (the "台湾通行证"), 9 characters: "前缀字母“L”+8位阿拉伯数字",
 * or `T` and 8 digits on the older permit. No check character is published. `masked()` keeps the last four characters
 * (no official masking rule).
 *
 * @see https://www.nia.gov.cn/n794014/n1050181/n1050479/c1197066/content.html 出入境证件简明手册
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CN.md "Exit-entry permit to Taiwan"
 */
final class ExitEntryPermitToTaiwanNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 9;

    private const string DOCUMENT = 'Chinese Taiwan exit-entry permit number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[LT][0-9]{8}')->parse($value, self::DOCUMENT);
    }
}
