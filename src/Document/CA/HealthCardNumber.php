<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Contract\StringConvertible;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Contract\IssuedBySubdivision;
use Override;

/**
 * The health card (health insurance) number of a province or territory; one class per jurisdiction in
 * `Document\CA\HealthCard\`, built from a bare number by {@see HealthCardNumberFactory}.
 *
 * Implemented by this package only; methods may be added in minor releases.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
interface HealthCardNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision
{
    #[Override]
    public function jurisdiction(): Jurisdiction;
}
