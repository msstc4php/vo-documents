<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * A health card number checked against its jurisdiction's published shapes ({@see shapes()}, quoted on each class)
 * after the separators are removed and ASCII letters upper-cased; a check digit is verified only where the algorithm
 * is published (BC, ON).
 *
 * `masked()` (no official rule) keeps the last four characters, three for a number of eight characters or fewer:
 * `*****6789`, `*****678`.
 *
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
abstract class AbstractHealthCardNumber extends AbstractStringValueObject implements HealthCardNumber
{
    private const int SHORT_NUMBER = 8;

    #[Override]
    final public function masked(): string
    {
        $value = Text::nonEmpty($this->value);

        return Mask::keepLastCharacters($value, \strlen($value) <= self::SHORT_NUMBER ? 3 : 4);
    }

    /**
     * Whether the number, with the separators removed, has a length one of this jurisdiction's shapes has.
     *
     * Protected, so it is no API of the jurisdiction classes: the factory reads it through `Internal\LengthProbe`.
     */
    final protected static function fitsLength(string $number): bool
    {
        return static::shapes()->fitsLength($number);
    }

    abstract protected static function shapes(): NumberShapes;

    /**
     * Applies the jurisdiction's rules beyond the shape and returns the stored form.
     *
     * @param non-empty-string $normalized a number that fits {@see shapes()}
     * @param non-empty-string $document the document's name in exception messages
     *
     * @return non-empty-string
     */
    protected function stored(string $normalized, string $document): string
    {
        return $normalized;
    }

    #[Override]
    final protected function parse(string $value): string
    {
        $document = $this->jurisdiction()->isoCode() . ' health card number';

        return $this->stored(static::shapes()->parse($value, $document), $document);
    }
}
