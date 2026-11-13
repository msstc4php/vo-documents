<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * A licence number checked against its jurisdiction's published shape ({@see format()}, cited on each class) after the
 * separators are removed and ASCII letters upper-cased; no jurisdiction publishes a check digit.
 *
 * `masked()` (no official rule) hides every character but the last min(3, ⌊length / 2⌋): `*******890`, `*2`. Three,
 * not the package's usual four, because AU numbers are short (at most 10 characters).
 *
 * @see docs/sources/AU.md "Driving licence numbers"
 */
abstract class AbstractDriverLicenceNumber extends AbstractStringValueObject implements DriverLicenceNumber
{
    private const int MAX_VISIBLE = 3;

    #[Override]
    final public function masked(): string
    {
        $value = Text::nonEmpty($this->value);

        return Mask::keepLastCharacters($value, \min(self::MAX_VISIBLE, \intdiv(\strlen($value), 2)));
    }

    /**
     * Whether the number, with the separators removed, has a length this jurisdiction's format allows.
     *
     * @internal for {@see \MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumberFactory}
     */
    final public static function fitsLength(string $number): bool
    {
        return static::format()->fitsLength($number);
    }

    abstract protected static function format(): NumberFormat;

    #[Override]
    final protected function parse(string $value): string
    {
        return static::format()->parse($value, $this->jurisdiction()->isoCode() . ' driver licence number');
    }
}
