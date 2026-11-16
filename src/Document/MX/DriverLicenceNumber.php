<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use MSSTC4PHP\ValueObject\Contract\StringConvertible;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Contract\IssuedBySubdivision;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use Override;

/**
 * The driver's licence number of a federal entity; one class per entity with a published format in
 * `Document\MX\DriverLicence\`, built from a bare number by {@see DriverLicenceNumberFactory}.
 *
 * @see docs/sources/MX.md "State driving licences (Task 6b)"
 */
interface DriverLicenceNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision
{
    #[Override]
    public function jurisdiction(): Jurisdiction;
}
