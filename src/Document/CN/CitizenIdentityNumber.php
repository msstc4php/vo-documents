<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CN;

use DateTimeImmutable;
use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Contract\HasBirthDate;
use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Internal\Gb11643;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * 公民身份号码, GB 11643-1999 (also the number of the Hong Kong, Macao and Taiwan resident permit, whose address codes
 * are 810000, 820000 and 830000): 18 characters, "六位数字地址码，八位数字出生日期码，三位数字顺序码和一位数字校验码". The
 * birth date is `YYYYMMDD` (the example `440524188001010014` is of 1880) and must be a calendar date; the sequence
 * code is odd for a man and even for a woman; the check character is ISO 7064:1983 MOD 11-2 (`X` for 10; a lower-case
 * `x` is stored as `X`). The 15-digit number of the first-generation card is refused by length: the NPC Standing
 * Committee's 2011 amendment of the Resident Identity Card Law stopped its use from 2013-01-01. The address code is
 * not checked against GB/T 2260. The standard writes the number "不留空格", so there is no display form; `masked()`
 * keeps the last four characters (no official masking rule): `**************002X`; they include the last digit of the
 * sequence code, so a masked number still tells the sex. A 2023 permanent-residence number (first digit 9) has the
 * same layout and is accepted here too; no rule tells them apart, so use {@see ForeignerPermanentResidenceNumber} for
 * one.
 *
 * @see http://zjw.beijing.gov.cn/Portals/0/zhuanti/fazhan/%E8%BA%AB%E4%BB%BD%E8%AF%81GB11643-1999.pdf GB 11643-1999
 *      §5.1-5.2, Annex A and B
 * @see https://www.gov.cn/zhengce/content/2018-08/19/content_5314865.htm 港澳台居民居住证申领发放办法 (国办发〔2018〕81号),
 *      Art. 3
 * @see docs/sources/CN.md "Citizen identity number"
 */
final class CitizenIdentityNumber extends AbstractStringValueObject implements HasBirthDate, HasSex, Maskable
{
    public const int MAX_LENGTH = 18;

    private const int SEQUENCE_LAST_DIGIT_INDEX = 16;

    private const string DOCUMENT = 'Chinese citizen identity number';

    /**
     * @return non-empty-string the six-digit 地址码 of the registered residence's county (GB/T 2260), e.g. "110105", or
     *                          "810000", "820000", "830000" on the Hong Kong, Macao and Taiwan resident permit
     */
    public function administrativeDivisionCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 6));
    }

    #[Override]
    public function birthDate(): DateTimeImmutable
    {
        return Gb11643::birthDate(Text::nonEmpty($this->value));
    }

    #[Override]
    public function sex(): Sex
    {
        return \in_array($this->value[self::SEQUENCE_LAST_DIGIT_INDEX], ['1', '3', '5', '7', '9'], true)
            ? Sex::Male
            : Sex::Female;
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = Gb11643::normalize($value, self::DOCUMENT);
        Gb11643::assertBirthDate($number, self::DOCUMENT);
        Gb11643::assertCheckCharacter($number, self::DOCUMENT);

        return $number;
    }
}
