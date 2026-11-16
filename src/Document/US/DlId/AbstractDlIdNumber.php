<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US\DlId;

use MSSTC4PHP\ValueObject\Documents\Document\US\DlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * A DL/ID number checked against its jurisdiction's published shapes ({@see shapes()}, quoted on each class) after
 * the separators are removed and ASCII letters upper-cased. Format only: no jurisdiction publishes a check-digit
 * algorithm, and data some numbers encode (name, birth date) is neither verified nor decoded.
 *
 * `masked()` (no official rule) keeps the last four characters, three for a number of eight characters or fewer:
 * `*****6789`, `*****678`; a class with a published display form masks in that layout.
 *
 * @see docs/sources/US.md "DL/ID numbers"
 */
abstract class AbstractDlIdNumber extends AbstractStringValueObject implements DlIdNumber
{
    private const int SHORT_NUMBER = 8;

    #[Override]
    final public function masked(): string
    {
        $value = Text::nonEmpty($this->value);

        return static::layout(Mask::keepLastCharacters($value, \strlen($value) <= self::SHORT_NUMBER ? 3 : 4));
    }

    /**
     * Whether the number, with the separators removed, has a length one of this jurisdiction's shapes has.
     *
     * @internal for {@see \MSSTC4PHP\ValueObject\Documents\Document\US\DlIdNumberFactory}
     */
    final public static function fitsLength(string $number): bool
    {
        return static::shapes()->fitsLength($number);
    }

    abstract protected static function shapes(): NumberShapes;

    /**
     * The published display form of a stored (or masked) number; the stored form where none is published.
     *
     * @param non-empty-string $compact
     *
     * @return non-empty-string
     */
    protected static function layout(string $compact): string
    {
        return $compact;
    }

    #[Override]
    final protected function parse(string $value): string
    {
        return static::shapes()->parse($value, $this->jurisdiction()->isoCode() . ' DL/ID number');
    }
}
