<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * ЕНП, the single number of the compulsory medical insurance (OMS) policy: 16 digits whose last one is the control
 * digit, "вычисляется арифметически в соответствии с методикой расчета, описанной в международном стандарте ISO/HL7
 * 27931:2009 (алгоритм Mod10)". The HL7 Mod10 check digit is the Luhn check digit (proof in RU.md). Old-form policies
 * (series and number, issued before 2011) are not this value object. `masked()` keeps the last four digits (no
 * official masking rule): `************3453`.
 *
 * The 16-digit length is not stated in the text of either act read (only an FFOMS news page says "из 16 цифр"); it is
 * the plan's value, see RU.md.
 *
 * @see https://www.ffoms.gov.ru/upload/iblock/56d/56d1f4edbcb3a5d37d20328a012d4e17.pdf FFOMS Order of 07.04.2011
 *      No. 79 (as amended 05.03.2020), register data table, note 1 (not reachable from the build environment; quoted
 *      from an aggregator copy in RU.md)
 * @see docs/sources/RU.md "OMS policy"
 */
final class OmsPolicyNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'OMS policy number';

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return Text::nonEmpty($this->value);
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 16, self::DOCUMENT);

        if (!Checksum::luhn($digits)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $digits;
    }
}
