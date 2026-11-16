<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA;

use MSSTC4PHP\ValueObject\Contract\StringConvertible;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Contract\IssuedBySubdivision;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use Override;

/**
 * The driver's licence number of a province or territory; one class per jurisdiction with a published format in
 * `Document\CA\DriverLicence\`, built from a bare number by {@see DriverLicenceNumberFactory}.
 *
 * @see docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
interface DriverLicenceNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision
{
    #[Override]
    public function jurisdiction(): Jurisdiction;
}
