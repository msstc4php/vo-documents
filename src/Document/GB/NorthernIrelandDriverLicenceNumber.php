<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Driver number (field 5) of a Northern Ireland licence issued by DVA: eight digits. Format only: no check digit or
 * structure is published. It never overlaps the 16-character {@see GreatBritainDriverLicenceNumber}. `masked()` keeps
 * the last three digits (no official masking rule): `*****678`.
 *
 * @see https://www.nidirect.gov.uk/articles/photocard-driving-licence-explained nidirect (DVA), "The photocard driving
 *      licence explained", "Driver number (5)"
 * @see docs/sources/GB.md "Driving licence number, Northern Ireland"
 */
final class NorthernIrelandDriverLicenceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Northern Ireland driving licence number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 3);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 8, self::DOCUMENT);
    }
}
