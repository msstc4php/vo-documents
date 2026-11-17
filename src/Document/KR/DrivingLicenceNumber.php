<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\KR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * 운전면허번호 as 도로교통공단 (KoROAD) writes it for licences issued since 2014-07-02: 12 digits, a two-digit region
 * number and the earlier ten digits, `11-95-255933-61` ("서울 95-255933-61 → 11-95-255933-61"). The region number is a
 * string accessor, not an ISO 3166-2 code: KoROAD lists 11 서울 … 26 울산 (a licence office-less 광주 takes 전남's 18), and
 * the list is not checked, because the issuing offices are renumbered over time. The meaning of the other groups
 * and the last two digits is not quoted, so they have no accessor and no rule. A number of an older licence that begins
 * with the region's name (`서울 95-255933-61`) is not covered. `masked()` keeps the last four digits (no official
 * masking rule): `********3361`.
 *
 * @see https://www.koroad.or.kr/kp_web/licNewsView.do?board_code=PRBBS_080&board_num=101697 도로교통공단 면허소식 2014-07-14
 * @see docs/sources/KR.md "Driving licence number"
 */
final class DrivingLicenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Korean driving licence number';

    /**
     * @return non-empty-string the two-digit region number (positions 1-2), e.g. "11"
     */
    public function regionCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
    }

    /**
     * @return non-empty-string `11-95-255933-61`
     */
    public function formatted(): string
    {
        return \sprintf(
            '%s-%s-%s-%s',
            \substr($this->value, 0, 2),
            \substr($this->value, 2, 2),
            \substr($this->value, 4, 6),
            \substr($this->value, 10),
        );
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 12, self::DOCUMENT);
    }
}
