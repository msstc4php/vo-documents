<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CN;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Number of the 往来港澳通行证 a mainland resident travels to Hong Kong and Macao with (the "港澳通行证"), 9 characters: `C`
 * and 8 digits, or, from the range the NIA announced on 2025-10-15, `C`, a letter other than `I` and `O`, and 7 digits
 * ("第二位改为顺序使用英文字母（I、O除外），第三位起仍然为阿拉伯数字，总位数仍为9位", starting at `CA0000001`). No check character is
 * published. `masked()` keeps the last four characters (no official masking rule).
 *
 * @see https://gat.fujian.gov.cn/ztzl/cryjfwwj/flfg_28281/201812/t20181205_4693182.htm 电子往来港澳通行证号码编制规则调整
 *      (source: 国家移民管理局, 2025-10-15)
 * @see https://www.nia.gov.cn/n794014/n1050181/n1050479/c1197066/content.html 出入境证件简明手册
 * @see docs/sources/CN.md "Exit-entry permit to Hong Kong and Macao"
 */
final class ExitEntryPermitToHongKongMacauNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Chinese Hong Kong and Macao exit-entry permit number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('C[0-9]{8}', 'C[A-HJ-NP-Z][0-9]{7}')->parse($value, self::DOCUMENT);
    }
}
