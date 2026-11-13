<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Series and number of the Russian national driving licence: "четырехзначная цифровая серия и шестизначный номер
 * удостоверения", printed `00 00 000000`. Licences of the 1999 form with Cyrillic letters in the series are not
 * accepted: the order in force describes digits only and repealed that form. The source defines no check digit and no
 * meaning of the series digits, so the value is validated by its shape only. `masked()` keeps the last four digits of
 * `formatted()` (no official masking rule): `** ** **3456`.
 *
 * @see docs/sources/RU.md "Driving licence" — MVD Order of 13.05.2009 No. 365 (registered by the Ministry of Justice
 *      10.07.2009, No. 14302), appendix 1 "Описание образца водительского удостоверения"
 */
final class DriverLicenceNumber extends AbstractStringValueObject implements Maskable
{
    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return \sprintf('%s %s %s', \substr($this->value, 0, 2), \substr($this->value, 2, 2), \substr($this->value, 4));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 10, 'Russian driving licence number');
    }
}
