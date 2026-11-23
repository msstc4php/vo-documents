<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\IN;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Ayushman Bharat Health Account number, "a random 14-digit number" (PIB explainer, Ministry of Health and Family
 * Welfare, 2024-04-04). Format only: no prefix or check digit is published. No official display form was read, so
 * there is no `formatted()`; hyphens and spaces in the input are dropped. `masked()` keeps the last four digits (no
 * official masking rule). The ABHA address (`name@abdm`) is a different identifier.
 *
 * @see https://www.pib.gov.in/PressReleasePage.aspx?PRID=2017129&reg=48&lang=2 PIB
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/IN.md "ABHA number"
 */
final class AbhaNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 14;

    private const string DOCUMENT = 'ABHA number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 14, self::DOCUMENT);
    }
}
