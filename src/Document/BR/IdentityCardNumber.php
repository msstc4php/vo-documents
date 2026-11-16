<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Contract\StringConvertible;
use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Contract\IssuedBySubdivision;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use Override;

/**
 * The Registro Geral (RG) number of a state Carteira de Identidade of the model before the CIN; one class per unit
 * with a published format in `Document\BR\IdentityCard\`, built from a bare number by
 * {@see IdentityCardNumberFactory}. The CIN's number is the CPF ({@see Cpf}), not a state RG.
 *
 * @see docs/sources/BR.md "RG per state (Task 7b)"
 */
interface IdentityCardNumber extends StringConvertible, ValueObject, Maskable, IssuedBySubdivision
{
    #[Override]
    public function jurisdiction(): Jurisdiction;
}
