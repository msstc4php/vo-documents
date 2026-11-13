<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\TR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * T.C. Kimlik No (Türkiye Cumhuriyeti kimlik numarası): eleven digits, also printed on the passport, the identity card
 * and the driving licence (field 4d). Format only: the last two digits are check digits, but NVİ shares their
 * algorithm with public bodies and does not publish it, so they are not verified, and no first-digit rule is checked.
 * The number encodes nothing (NVİ: no sex, age, religion or birthplace). `masked()` keeps the last four digits (no
 * official masking rule): `*******8901`.
 *
 * @see https://www.nvi.gov.tr/tokat/tc-kimlik-no-islemleri NVİ, "T.C. Kimlik No İşlemleri", questions 2 and 3
 * @see https://www.mevzuat.gov.tr/MevzuatMetin/3.5.200611081.pdf Nüfus Hizmetleri Kanununun Uygulanmasına İlişkin
 *      Yönetmelik, Madde 4 (p) ("onbir rakamdan oluşan")
 * @see docs/sources/TR.md "T.C. Kimlik No"
 */
final class IdentityNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Turkish identity number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 11, self::DOCUMENT);
    }
}
