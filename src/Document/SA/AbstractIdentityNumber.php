<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\SA;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * The ten digits shared by the Saudi national ID number and the iqama number. The first digit routes a number to its
 * class (1: national ID, 2: iqama, ZATCA BR-KSA-F-10/F-11) but is not checked: ZATCA states the rules only as
 * warnings on its developer forum, and no government text makes them a format rule, so a number with another first
 * digit is accepted (docs/sources/SA.md). No check digit is published, so none is checked. `masked()` keeps the last
 * four digits (no official masking rule).
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

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, self::LENGTH, $this->document());
    }
}
