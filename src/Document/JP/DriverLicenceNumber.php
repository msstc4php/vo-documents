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
 * 運転免許証の番号, "12桁の数字" (警察庁丁運発第76号, 令和7年3月10日, section 1): 公安委員会コード (2 digits), 年別記号
 * (2 digits, the last two digits of the year of issue), 交付等番号 (6 digits, a running number per year), チェックデジット
 * (1 digit) and 再交付記号 (1 digit). The マイナ免許証's 免許情報記録の番号 has the same layout (its last digit is the 再記録記号)
 * and is accepted too. Numbers are kept on renewal, so one issued under the 1981 notice has the same shape.
 *
 * The check digit is not verified: the notice names only the "モジュラス11" method and gives no weights. The commission
 * code is a string, not an ISO 3166-2 code: Hokkaido has five commissions, and the code table is in another notice
 * (丁運発第112号 別添2) that is not checked here. The year is the two digits as written, with no century. `masked()` keeps
 * the last four digits (no official masking rule).
 *
 * @see https://www.npa.go.jp/laws/notification/menkyo20250310_76.pdf 警察庁交通局運転免許課長通達
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/JP.md "Driving licence number"
 */
final class DriverLicenceNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 12;

    private const string DOCUMENT = 'Japanese driving licence number';

    /**
     * @return non-empty-string the two digits of the issuing 公安委員会 (positions 1-2), e.g. "30"
     */
    public function publicSafetyCommissionCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
    }

    /**
     * @return non-empty-string the 年別記号 (positions 3-4): the last two digits of the year of issue, no century
     */
    public function yearSymbol(): string
    {
        return Text::nonEmpty(\substr($this->value, 2, 2));
    }

    /**
     * @return non-empty-string the 交付等番号 (positions 5-10): the running number within the year
     */
    public function serialNumber(): string
    {
        return Text::nonEmpty(\substr($this->value, 4, 6));
    }

    /**
     * @return non-empty-string the 再交付記号 (position 12): "0" at first issue, then the count of reissues, reset to
     *                          "1" when the count would reach 10
     */
    public function reissueSymbol(): string
    {
        return Text::nonEmpty($this->value[11]);
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 12, self::DOCUMENT);
    }
}
