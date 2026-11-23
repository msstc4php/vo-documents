<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\IN;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Passport number as the data page prints it: 8 characters (the MEA's database structure for the data page,
 * "Passport Number - CHAR(8)"; specimens `J1234002` and `B5007771`). Only the length and ASCII letters and digits
 * are enforced: no MEA text read states the split into letters and digits, so one letter and seven digits (the
 * specimens' shape) is not a rule. No check character is published. `masked()` keeps the last three characters (no
 * official masking rule; the package keeps three of a number of eight characters or fewer).
 *
 * @see https://www.mea.gov.in/Portal/Tender/3234_1/1_corrigendum_dated_26.05.2017-1.pdf MEA, 4.4
 * @see docs/sources/IN.md "Passport number"
 */
final class PassportNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Indian passport number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 3);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return new NumberShapes('[A-Z0-9]{8}')->parse($value, self::DOCUMENT);
    }
}
