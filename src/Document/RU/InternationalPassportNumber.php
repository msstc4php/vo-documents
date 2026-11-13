<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Series and number of the passport of a citizen of the Russian Federation for travel abroad: "серия и номер единым
 * 9-значным числом" (MRZ document number), shown as a two-digit series and a seven-digit number (`75 1234567`). The
 * number's own check digit lives in the MRZ, not in the number, so the value is validated by its shape only.
 * `masked()` keeps the last four digits of `formatted()` (no official masking rule): `** ***4567`.
 *
 * @see http://publication.pravo.gov.ru/document/0001202105190040 MVD Order of 31.03.2021 No. 186, appendix 12
 *      (MRZ lower line, positions 1-9)
 * @see http://publication.pravo.gov.ru/document/0001202002280021 MVD Order of 31.12.2019 No. 996 (passport with chip)
 * @see docs/sources/RU.md "International passport"
 */
final class InternationalPassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Russian international passport number';

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return \sprintf('%s %s', \substr($this->value, 0, 2), \substr($this->value, 2));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 9, self::DOCUMENT);
    }
}
