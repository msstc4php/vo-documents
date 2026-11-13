<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Number of the carte nationale d'identité issued until August 2021: twelve digits, the year and month of issue
 * (`YYMM`, month `01`-`12`), the INSEE geographic code of the place of issue (3 digits) and an order number
 * (5 digits). The number has no check digit of its own; the one that follows it in the MRZ belongs to the MRZ. The card
 * issued since August 2021 is not covered (no official format). `masked()` keeps the last four digits (no official
 * masking rule): `********2345`.
 *
 * @see https://www.gisti.org/IMG/pdf/norintd0000001c.pdf Ministère de l'intérieur, instruction générale relative à la
 *      carte nationale d'identité, NOR INTD0000001C, rubriques 46 and 55 (copy; Légifrance answers 403)
 * @see docs/sources/FR.md "Carte nationale d'identité"
 */
final class LegacyIdentityCardNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'French identity card number';

    /**
     * @return non-empty-string the last two digits of the year of issue
     */
    public function issueYearDigits(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
    }

    /**
     * @return int the month of issue, 1-12 (checked on parsing)
     */
    public function issueMonth(): int
    {
        return (int) \substr($this->value, 2, 2);
    }

    /**
     * @return non-empty-string the INSEE code of the place of issue, positions 5-7
     */
    public function issuePlaceCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 4, 3));
    }

    /**
     * @return non-empty-string positions 8-12
     */
    public function serialNumber(): string
    {
        return Text::nonEmpty(\substr($this->value, 7));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = Digits::exactly($value, 12, self::DOCUMENT);
        $month = (int) \substr($number, 2, 2);
        if ($month < 1 || $month > 12) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid issue month).', self::DOCUMENT));
        }

        return $number;
    }
}
