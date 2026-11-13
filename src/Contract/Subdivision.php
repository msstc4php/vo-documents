<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

use BackedEnum;

/**
 * A string-backed enum whose value is the ISO 3166-2 code.
 */
interface Subdivision extends BackedEnum
{
    /**
     * ISO 3166-2 code, e.g. AU-NSW.
     *
     * @return non-empty-string
     */
    public function isoCode(): string;
}
