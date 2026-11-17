<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\KR;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Normalizer;
use Override;

/**
 * 운전면허번호 as 도로교통공단 (KoROAD) writes it. Two forms, each kept as it is:
 * - from 2014-07-02: 12 digits, a two-digit region number and the earlier ten digits, stored as `119525593361`
 *   (display form `11-95-255933-61`, "서울 95-255933-61 → 11-95-255933-61");
 * - before it: the region's name and ten digits, stored as KoROAD prints it, `서울 95-255933-61`. The names are those of
 *   KoROAD's table (서울 부산 경기 강원 충북 충남 전북 전남 경북 경남 제주 대구 인천 광주 대전 울산); another name is refused.
 *
 * {@see regionCode()} gives the region number in both (the name through KoROAD's table: 서울 11 … 울산 26, 광주 24). It is
 * a string, not an ISO 3166-2 code, and the number of a new licence is not checked against the table because the
 * offices are renumbered over time. The meaning of the other groups and of the last two digits is not quoted, so they
 * have no accessor and no rule. `masked()` keeps the last four digits (no official masking rule).
 *
 * @see https://www.koroad.or.kr/kp_web/licNewsView.do?board_code=PRBBS_080&board_num=101697 도로교통공단 면허소식 2014-07-14
 * @see docs/sources/KR.md "Driving licence number"
 */
final class DrivingLicenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Korean driving licence number';

    /** @var array<string, non-empty-string> region name on a licence issued before 2014-07-02 => region number */
    private const array REGIONS = [
        '서울' => '11', '부산' => '12', '경기' => '13', '강원' => '14', '충북' => '15', '충남' => '16',
        '전북' => '17', '전남' => '18', '경북' => '19', '경남' => '20', '제주' => '21', '대구' => '22',
        '인천' => '23', '광주' => '24', '대전' => '25', '울산' => '26',
    ];

    /**
     * @return non-empty-string the two-digit region number, e.g. "11" (for `서울 95-255933-61` too)
     */
    public function regionCode(): string
    {
        if ($this->isNumbered()) {
            return Text::nonEmpty(\substr($this->value, 0, 2));
        }

        $region = \mb_substr($this->value, 0, 2, 'UTF-8');

        return self::REGIONS[$region] ?? throw new LogicException('Region name validated by parse().');
    }

    /**
     * @return non-empty-string `11-95-255933-61`, or `서울 95-255933-61` for a licence of the earlier form
     */
    public function formatted(): string
    {
        if (!$this->isNumbered()) {
            return Text::nonEmpty($this->value);
        }

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
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $composed = Normalizer::normalize($value, Normalizer::FORM_C);
        $stripped = Digits::strip(\is_string($composed) ? $composed : $value);
        if (\preg_match('/^(\D{2})([0-9]{10})$/u', $stripped, $parts) === 1 && isset(self::REGIONS[$parts[1]])) {
            return \sprintf(
                '%s %s-%s-%s',
                $parts[1],
                \substr($parts[2], 0, 2),
                \substr($parts[2], 2, 6),
                \substr($parts[2], 8),
            );
        }

        return Digits::exactlyStripped($stripped, 12, self::DOCUMENT);
    }

    private function isNumbered(): bool
    {
        return \ctype_digit($this->value[0]);
    }
}
