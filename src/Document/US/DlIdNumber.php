<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Contract\StringConvertible;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Contract\IssuedBySubdivision;
use Override;

/**
 * The DL/ID number (AAMVA "customer identifier", field 4d / element DAQ) of a driver licence or ID card issued by a
 * state or DC; one class per jurisdiction with a published format in `Document\US\DlId\`, built from a bare number by
 * {@see DlIdNumberFactory}.
 *
 * Implemented by this package only; methods may be added in minor releases.
 *
 * @see docs/sources/US.md "DL/ID numbers"
 */
interface DlIdNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision
{
    #[Override]
    public function jurisdiction(): Jurisdiction;
}
