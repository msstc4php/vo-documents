<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

use DateTimeImmutable;

/**
 * Implemented by this package only; methods may be added in minor releases.
 */
interface HasBirthDate
{
    /** Midnight UTC. */
    public function birthDate(): DateTimeImmutable;
}
