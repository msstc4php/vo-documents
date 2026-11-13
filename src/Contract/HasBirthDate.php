<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

use DateTimeImmutable;

interface HasBirthDate
{
    /** Midnight UTC. */
    public function birthDate(): DateTimeImmutable;
}
