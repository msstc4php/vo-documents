<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CN;

use DateTimeImmutable;
use MSSTC4PHP\ValueObject\Documents\Contract\HasBirthDate;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Gb11643;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Number of the foreigner's permanent residence ID card issued from 2023-12-01 (外国人永久居留身份证, NIA): 18
 * characters "参照居民身份证“公民身份号码”国家标准", "17 位数字+1 位校验位". The first digit is "9", "作为外国人的身份标识码", then
 * the 2-digit code of the place of application, the 3-digit nationality code, the birth date `YYYYMMDD`, a 3-digit
 * sequence code and a MOD 11-2 check character "与公民身份号码校验方式相同" (the NIA's example is
 * `932682198501010017`). The nationality and application codes are strings: the NIA's text does not name the code lists
 * (682 is Saudi Arabia, 32 Jiangsu in its example). No rule ties the sequence code to the sex. The 15-character number
 * of earlier cards is {@see LegacyForeignerPermanentResidenceNumber}. `masked()` keeps the last four characters (no
 * official masking rule).
 *
 * @see https://www.nia.gov.cn/20231013/2.pdf 国家移民管理局, 附件 2 "新版外国人永久居留身份证适配性改造要点", 二
 * @see docs/sources/CN.md "Foreigner's permanent residence ID card"
 */
final class ForeignerPermanentResidenceNumber extends AbstractStringValueObject implements HasBirthDate, Maskable
{
    private const string DOCUMENT = 'Chinese permanent residence card number';

    /**
     * @return non-empty-string the two digits after the leading "9" (the "受理地代码", the province in the NIA's example)
     */
    public function applicationPlaceCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 1, 2));
    }

    /**
     * @return non-empty-string the three-digit "国家和地区代码" of the holder, e.g. "682"
     */
    public function nationalityCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 3, 3));
    }

    #[Override]
    public function birthDate(): DateTimeImmutable
    {
        return Gb11643::birthDate(Text::nonEmpty($this->value));
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
        if ($number[0] !== '9') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        Gb11643::assertBirthDate($number, self::DOCUMENT);
        Gb11643::assertCheckCharacter($number, self::DOCUMENT);

        return $number;
    }
}
