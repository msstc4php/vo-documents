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
 * accepted: Order 365 repealed that form from 01.03.2011 and kept it valid only until expiry (ten years), so none is
 * valid any more. The source defines no check digit and no meaning of the series digits, so the value is validated by
 * its shape only. `masked()` keeps the last four digits of `formatted()` (no official masking rule): `** ** **3456`.
 *
 * @see docs/sources/RU.md "Driving licence" — MVD Order of 13.05.2009 No. 365 (registered by the Ministry of Justice
 *      10.07.2009, No. 14302; cited by number, predates the portal), appendix 1 "Описание образца водительского
 *      удостоверения", §8, §10
 */
final class DriverLicenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Russian driving licence number';

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
        return Digits::exactly($value, 10, self::DOCUMENT);
    }
}
