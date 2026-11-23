<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\KR;

use DateTimeImmutable;
use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Contract\HasBirthDate;
use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Internal\KoreanRegistrationNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * 주민등록번호, 13 digits (주민등록법 시행규칙 Art. 2: "생년월일ㆍ성별 등을 표시할 수 있는 13자리의 숫자"): the birth date
 * `YYMMDD` and a digit for the sex and century. 국가기록원: "1은 남자, 2는 여자이다", and "2000년 출생자부터는 남자는 3, 여자는 4를
 * 부여받았다"; the digits 9 (man) and 0 (woman) for a birth in the 1800s are quoted from secondary sources only, see
 * docs/sources/KR.md. The birth date must exist in that century. Digits 5-8 belong to the foreigner registration number
 * ({@see ForeignerRegistrationNumber}) and are refused here. No check digit is verified: the formula is not quoted,
 * and a number issued since 2020-10 has six random digits after the seventh ("성별 표시 첫 자리를 제외하고 6자리의
 * 임의번호", 행정안전부). No display form is quoted, so there is no `formatted()`; `masked()` keeps the last four digits (no
 * official masking rule): `*********4567`.
 *
 * @see https://theme.archives.go.kr/next/koreaOfRecord/identityCard.do 국가기록원
 * @see https://www.korea.kr/news/policyNewsView.do?newsId=148872723 행정안전부 2020-05-25
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/KR.md "Resident registration number"
 */
final class ResidentRegistrationNumber extends AbstractStringValueObject implements HasBirthDate, HasSex, Maskable
{
    public const int MAX_LENGTH = 13;

    private const string DOCUMENT = 'Korean resident registration number';

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
        return KoreanRegistrationNumber::normalize($value, self::DOCUMENT, '123409');
    }
}
