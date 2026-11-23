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
 * `masked()` (no official rule) keeps the last four characters (`*****6782`), and never the whole significant value:
 * leading zeros are padding (SP stores nine characters), so a value of n characters without them keeps n − 1
 * (`000000012` → `********2`, `000000103` → `*******03`).
 *
 * @see docs/sources/BR.md "RG per state (Task 7b)"
 */
abstract class AbstractIdentityCardNumber extends AbstractStringValueObject implements IdentityCardNumber
{
    private const int VISIBLE = 4;

    #[Override]
    final public function masked(): string
    {
        $value = Text::nonEmpty($this->value);
        $significant = \strlen(\ltrim($value, '0'));

        return Mask::keepLastCharacters($value, \min(self::VISIBLE, $significant - 1));
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

        return $this->stored(static::shapes()->parse($this->withoutDots($value), $document), $document);
    }

    /**
     * The printed form `12.345.678-9` separates groups with dots, as the CPF does.
     */
    private function withoutDots(string $number): string
    {
        return \str_replace('.', '', $number);
    }
}
