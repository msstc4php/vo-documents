<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;

/**
 * The Latin form a composite name gives ({@see PersonName::latin()}) and the override it keeps, null when the
 * effective form is the derived one.
 *
 * @internal
 */
final readonly class LatinForm
{
    public function __construct(
        public IcaoPersonName $effective,
        public ?IcaoPersonName $override,
    ) {
    }
}
