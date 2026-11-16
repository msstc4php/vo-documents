<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * A driver's licence number checked against its entity's published shapes ({@see shapes()}, quoted on each class)
 * after the separators are removed and ASCII letters upper-cased. No entity publishes a check digit.
 *
 * `masked()` (no official rule) keeps the last four characters (plan default; every covered shape is longer than
 * eight characters, so the three-character case of the default does not arise yet).
 *
 * @see docs/sources/MX.md "State driving licences (Task 6b)"
 */
abstract class AbstractDriverLicenceNumber extends AbstractStringValueObject implements DriverLicenceNumber
{
    #[Override]
    final public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    /**
     * Whether the number, with the separators removed, has a length one of this entity's shapes has.
     *
     * @internal for {@see \MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicenceNumberFactory}
     */
    final public static function fitsLength(string $number): bool
    {
        return static::shapes()->fitsLength($number);
    }

    abstract protected static function shapes(): NumberShapes;

    #[Override]
    final protected function parse(string $value): string
    {
        return static::shapes()->parse($value, $this->jurisdiction()->isoCode() . " driver's licence number");
    }
}
