<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\KR;

use DateTimeImmutable;
use MSSTC4PHP\ValueObject\Documents\Contract\HasBirthDate;
use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\KoreanRegistrationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * 외국인등록번호, 13 digits (출입국관리법 시행규칙 별표 2 "외국인등록번호의 구성체계(제46조제3항 관련)": 생년월일 (6 digits), 성별구분번호,
 * 등록연도번호, 등록기관번호, 일련번호). The 별표 gives the layout as a figure. The sex comes from the seventh digit, as 법무부 says
 * (보도자료 2023-03-28): "외국인등록번호 13자리 중 7번째 숫자가 5․7은 남성, 6․8은 여성". The release does not give the century, so
 * 5 and 6 for a birth in the 1900s and 7 and 8 for the 2000s are quoted from secondary sources only (docs/sources/KR.md).
 * The birth date must exist in that century. Digits 1-4, 9 and 0 belong to the resident registration number
 * ({@see ResidentRegistrationNumber}) and are refused here. No check digit is verified (none is published);
 * `masked()` keeps the last four digits (no official masking rule).
 *
 * @see https://law.go.kr/LSW/flDownload.do?flSeq=89453591&bylClsCd=110201 출입국관리법 시행규칙 별표 2
 * @see https://www.moj.go.kr/bbs/moj/182/568998/artclView.do 법무부 보도자료, 2023-03-28
 * @see docs/sources/KR.md "Foreigner registration number"
 */
final class ForeignerRegistrationNumber extends AbstractStringValueObject implements HasBirthDate, HasSex, Maskable
{
    private const string DOCUMENT = 'Korean foreigner registration number';

    #[Override]
    public function birthDate(): DateTimeImmutable
    {
        return KoreanRegistrationNumber::birthDate(Text::nonEmpty($this->value));
    }

    #[Override]
    public function sex(): Sex
    {
        return KoreanRegistrationNumber::sex(Text::nonEmpty($this->value));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return KoreanRegistrationNumber::normalize($value, self::DOCUMENT, '5678');
    }
}
