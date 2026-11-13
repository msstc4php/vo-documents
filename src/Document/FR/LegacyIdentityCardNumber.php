<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Number of the carte nationale d'identité issued until August 2021: twelve characters, the year and month of issue
 * (`YYMM`, not validated: the source states no rule for the field's values), the INSEE geographic code of the place
 * of issue (3 characters: three digits, or the Corsican department `2A`/`2B` and a digit) and an order number
 * (5 digits). The instruction says "douze chiffres", but the INSEE code of a Corsican place starts with `2A`/`2B`, and
 * a card still valid must not be refused. The number has no check digit of its own; the one that follows
 * it in the MRZ belongs to the MRZ. The card issued since August 2021 is not covered (no official format). `masked()`
 * keeps the last four digits (no official masking rule): `********2345`.
 *
 * @see NOR INTD0000001C Ministère de l'intérieur, instruction générale relative à la carte nationale d'identité
 *      (2000), rubriques 46 and 55
 * @see https://www.gisti.org/IMG/pdf/norintd0000001c.pdf mirror of NOR INTD0000001C (not an official host)
 * @see docs/sources/FR.md "Carte nationale d'identité"
 */
final class LegacyIdentityCardNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'French identity card number';

    private const array DIGITS = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
    ];

    /**
     * @return non-empty-string the last two digits of the year of issue
     */
    public function issueYearDigits(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
    }

    /**
     * @return int<0, 99> positions 3-4 read as a number, the month of issue; not validated, so it may lie outside 1-12
     */
    public function issueMonth(): int
    {
        return self::DIGITS[$this->value[2]] * 10 + self::DIGITS[$this->value[3]];
    }

    /**
     * @return non-empty-string the INSEE code of the place of issue, positions 5-7, e.g. "923" or "2A1"
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
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9AB', 12, 12)->parse($value, self::DOCUMENT);
        $department = \substr($number, 4, 2);
        if (
            \strspn($number, '0123456789', 0, 4) !== 4
            || \strspn($number, '0123456789', 6) !== 6
            || ($department !== '2A' && $department !== '2B' && \strspn($department, '0123456789') !== 2)
        ) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
