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
 * Number of an ordinary passport (普通护照), 9 characters, as the NIA writes them: `E` and 8 digits for an electronic
 * passport issued before April 2017; from then `E`, a letter other than `I` and `O`, and 7 digits ("第二位改为顺序使用英文字母
 * （I、O除外），第三位起仍然为阿拉伯数字，总位数仍为9位", starting at `EA0000001`); `G` and 8 digits for the older paper
 * passport. Passports of other types (official, diplomatic, for public affairs) have no published format and are not
 * covered. No check character is published. `masked()` keeps the last four characters (no official masking rule).
 *
 * @see https://www.nia.gov.cn/n794014/n1050181/n1050479/c1197066/content.html 出入境证件简明手册
 * @see https://www.cac.gov.cn/2017-04/07/c_1120766993.htm 公安部网站, 2017-04-07
 * @see docs/sources/CN.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Chinese passport number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('E[0-9]{8}', 'E[A-HJ-NP-Z][0-9]{7}', 'G[0-9]{8}')->parse($value, self::DOCUMENT);
    }
}
