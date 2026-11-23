<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\IN;

use LogicException;
use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Permanent Account Number, ten characters as the Income Tax Department describes them: three letters of the
 * "alphabetic series running from AAA to ZZZ", the status of the holder (one of the ten letters of
 * {@see PanHolderType}), the first letter of the holder's surname or name, "sequential numbers running from 0001 to
 * 9999" (so `0000` is refused) and "an alphabetic check digit". The fifth character is not checked against a name and
 * no formula for the check letter is published, so it is any letter. `masked()` keeps the last four characters (no
 * official masking rule); PAN is written without separators, so there is no `formatted()`.
 *
 * @see https://web.archive.org/web/2025/https://incometaxindia.gov.in/tutorials/1.permanent%20account%20number%20(pan).pdf Income Tax Department
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/IN.md "PAN"
 */
final class PermanentAccountNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 10;

    private const string DOCUMENT = 'Permanent Account Number';

    public function holderType(): PanHolderType
    {
        return PanHolderType::tryFrom($this->value[3])
            ?? throw new LogicException('Holder type letter validated by parse().');
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $pan = new NumberShapes('[A-Z]{3}[PCHABGJLFT][A-Z][0-9]{4}[A-Z]')->parse($value, self::DOCUMENT);

        if (\substr($pan, 5, 4) === '0000') {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid sequence number).', self::DOCUMENT));
        }

        return $pan;
    }
}
