<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * СНИЛС, the insurance number of an individual personal account: 11 digits, `XXX-XXX-XXX CC`. The control number CC
 * is the remainder of the sum of the first nine digits weighted 9..1 divided by 101, last two digits (100 → 00); it is
 * checked only for numbers above 001-001-998. A number of zeros only is refused (SFR check ВС.ЕФС-СЗВ-ТД.1.15).
 * `masked()` keeps the last four digits of `formatted()` (no official masking rule): `***-***-*45 95`.
 *
 * @see http://publication.pravo.gov.ru/document/0001202512190014 SFR Order of 17.11.2025 No. 1463: type
 *      ТипСтраховойНомер, checks ВСЗЛ.ОП.1.1 and ВС.ЕФС-СЗВ-ТД.1.15
 * @see docs/sources/RU.md "SNILS" — the algorithm the check ВСЗЛ.ОП.1.1 names, PFR "Правила проверки документов
 *      индивидуального (персонифицированного) учета", supplement 2 (information message of the PFR of 21.02.2013), §8
 */
final class Snils extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'SNILS';

    private const int LAST_UNCHECKED_NUMBER = 1001998;

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return \sprintf(
            '%s-%s-%s %s',
            \substr($this->value, 0, 3),
            \substr($this->value, 3, 3),
            \substr($this->value, 6, 3),
            \substr($this->value, 9),
        );
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 11, self::DOCUMENT);
        $number = \substr($digits, 0, 9);

        if ($digits === '00000000000') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (zeros only).', self::DOCUMENT));
        }

        if ((int) $number > self::LAST_UNCHECKED_NUMBER) {
            $control = Checksum::weightedMod($number, [9, 8, 7, 6, 5, 4, 3, 2, 1], 101) % 100;
            if ($control !== (int) \substr($digits, 9)) {
                throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
            }
        }

        return $digits;
    }
}
