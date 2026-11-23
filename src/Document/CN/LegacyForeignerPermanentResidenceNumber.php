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
 * Number of the foreigner's permanent residence permit and of the 2017 ID card (NIA): "15 位（3 位拉丁字母+12 位数字）", the
 * letters being the holder's country or region, then a 4-digit address code, a 6-digit `YYMMDD` birth date, a
 * sequence digit and a check digit (`KAZ110090123105`). Format only: the NIA names no check algorithm, and the
 * two-digit year leaves the date unchecked. Cards of this form stay valid until they expire. `masked()` keeps the last
 * four characters (no official masking rule).
 *
 * @see https://www.nia.gov.cn/20231013/2.pdf 国家移民管理局, 附件 2, 表 1 "既往版本永居证号码"
 * @see docs/sources/CN.md "Foreigner's permanent residence ID card"
 */
final class LegacyForeignerPermanentResidenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Chinese legacy permanent residence card number';

    /**
     * @return non-empty-string the three letters of the holder's country or region, e.g. "KAZ"; the NIA does not name
     *                          the code list
     */
    public function nationalityCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 3));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[A-Z]{3}[0-9]{12}')->parse($value, self::DOCUMENT);
    }
}
