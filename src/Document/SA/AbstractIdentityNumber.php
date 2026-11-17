<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\SA;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * The ten digits shared by the Saudi national ID number and the iqama number, told apart by the first digit. No check
 * digit is published, so none is checked. `masked()` keeps the last four digits (no official masking rule).
 *
 * @internal
 */
abstract class AbstractIdentityNumber extends AbstractStringValueObject implements Maskable
{
    private const int LENGTH = 10;

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    /**
     * @return non-empty-string e.g. "Saudi Arabian iqama number"
     */
    abstract protected function document(): string;

    /**
     * @return '1'|'2'
     */
    abstract protected function firstDigit(): string;

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, self::LENGTH, $this->document());

        if ($digits[0] !== $this->firstDigit()) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid first digit).', $this->document()));
        }

        return $digits;
    }
}
