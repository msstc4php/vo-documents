<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * A driver's licence number checked against its jurisdiction's published shapes ({@see shapes()}, quoted on each
 * class) after the separators are removed and ASCII letters upper-cased. No jurisdiction publishes a check digit.
 *
 * `masked()` (no official rule) keeps the last four characters, three for a number of eight characters or fewer:
 * `*****6789`, `*****678`; a class with a published display form masks in that layout.
 *
 * @internal extended by this package's jurisdiction classes only, its protected hooks are no API: type-hint
 *           {@see DriverLicenceNumber}
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/CA.md "Driver's licence numbers — per jurisdiction"
 */
abstract class AbstractDriverLicenceNumber extends AbstractStringValueObject implements DriverLicenceNumber
{
    private const int SHORT_NUMBER = 8;

    #[Override]
    final public function masked(): string
    {
        $value = Text::nonEmpty($this->value);

        return static::layout(Mask::keepLastCharacters($value, \strlen($value) <= self::SHORT_NUMBER ? 3 : 4));
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
        $document = $this->jurisdiction()->isoCode() . " driver's licence number";

        return $this->stored(static::shapes()->parse($value, $document), $document);
    }
}
