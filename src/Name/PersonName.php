<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Contract\ValueObject;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;

/**
 * A national full name that can give itself in Latin as ICAO Doc 9303 writes it in a passport.
 */
interface PersonName extends ValueObject
{
    public function latin(): IcaoPersonName;

    /**
     * @return non-empty-string every part in the national order, e.g. "Иванов Иван Иванович"
     */
    public function full(): string;
}
