<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * "Код подразделения" in the passport of a citizen of the Russian Federation: the six-digit code of the MVD migration
 * unit that issued it, written `NNN-NNN` on the passport and its stamps and "единым 6-значным числом без дефиса" in the
 * machine-readable zone. The source assigns the code to a unit but publishes no structure and no check digit, so the
 * value is validated by its shape only. `masked()` keeps the last four digits of `formatted()` (no official masking
 * rule): `**2-078`.
 *
 * @see http://publication.pravo.gov.ru/Document/View/0001202012310102 MVD Order of 16.11.2020 No. 773, §183.2.2 and the
 *      MRZ structure (lower line, positions 29-42)
 * @see docs/sources/RU.md "Passport issuing unit code"
 */
final class PassportIssuingUnitCode extends AbstractStringValueObject implements Maskable
{
    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return \sprintf('%s-%s', \substr($this->value, 0, 3), \substr($this->value, 3));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 6, 'Russian passport issuing unit code');
    }
}
