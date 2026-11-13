<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU;

use MSSTC4PHP\ValueObject\Contract\StringConvertible;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Contract\IssuedBySubdivision;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use Override;

/**
 * The licence number (not the card number) of a driver licence issued by an Australian state or territory; one class
 * per jurisdiction in `Document\AU\DriverLicence\`, built from a bare number by {@see DriverLicenceNumberFactory}.
 *
 * @see docs/sources/AU.md "Driving licence numbers"
 */
interface DriverLicenceNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision
{
    #[Override]
    public function jurisdiction(): Jurisdiction;
}
