<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

/**
 * Implemented by this package only; methods may be added in minor releases.
 */
interface IssuedBySubdivision
{
    public function jurisdiction(): Subdivision;
}
