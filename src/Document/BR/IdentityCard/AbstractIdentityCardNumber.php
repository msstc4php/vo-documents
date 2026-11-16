<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard;

use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * An RG number checked against its unit's published shapes ({@see shapes()}, quoted on each class) after the
 * separators and dots are removed and ASCII letters upper-cased; a check character is verified only where the
 * algorithm is published (SP).
 *
 * `masked()` (no official rule) keeps the last four characters (`*****6782`): every covered unit stores nine. When a
 * unit with shorter values is covered, restore the rule "three for eight characters or fewer, never the whole value",
 * with a test.
 *
 * @see docs/sources/BR.md "RG per state (Task 7b)"
 */
abstract class AbstractIdentityCardNumber extends AbstractStringValueObject implements IdentityCardNumber
{
    private const int VISIBLE = 4;

    #[Override]
    final public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), self::VISIBLE);
    }

    /**
     * Whether the number, with the separators and dots removed, has a length one of this unit's shapes has.
     *
     * @internal for {@see \MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCardNumberFactory}
     */
    final public static function fitsLength(string $number): bool
    {
        return static::shapes()->fitsLength(self::withoutDots($number));
    }

    abstract protected static function shapes(): NumberShapes;

    /**
     * Applies the unit's rules beyond the shape and returns the stored form.
     *
     * @param non-empty-string $normalized a number that fits {@see shapes()}
     * @param non-empty-string $document the document's name in exception messages
     *
     * @return non-empty-string
     */
    abstract protected function stored(string $normalized, string $document): string;

    #[Override]
    final protected function parse(string $value): string
    {
        $document = $this->jurisdiction()->isoCode() . ' identity card number';

        return $this->stored(static::shapes()->parse(self::withoutDots($value), $document), $document);
    }

    /**
     * The printed form `12.345.678-9` separates groups with dots, as the CPF does.
     */
    private static function withoutDots(string $number): string
    {
        return \str_replace('.', '', $number);
    }
}
