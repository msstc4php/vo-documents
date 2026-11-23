<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

use MSSTC4PHP\ValueObject\Documents\Sex;

/**
 * Implemented by this package only; methods may be added in minor releases.
 */
interface HasSex
{
    public function sex(): Sex;
}
