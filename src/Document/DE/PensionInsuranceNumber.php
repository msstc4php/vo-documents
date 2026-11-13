<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\DE;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Versicherungsnummer of the statutory pension insurance (Rentenversicherungsnummer), `NN DDMMYY L SS C`: area
 * number, birth date field, initial of the birth name, serial number and check digit (VKVV § 2). The check digit is
 * verified: the letter becomes its position in the alphabet (`A` = 01 … `Z` = 26), the twelve digits are weighted
 * 2, 1, 2, 5, 7, 1, 2, 1, 2, 1, 2, 1, the digit sums of the products are added, and the sum mod 10 is the check digit.
 *
 * The fields are exposed as printed and are not typed: the birth date field has no century and follows unpublished
 * rules for an unproven birth date (so it is not validated as a date), and the serial range 50-99 stands for female,
 * no entry or "divers" (so it is no {@see \MSSTC4PHP\ValueObject\Documents\Sex}). `masked()` keeps the last four
 * characters (no official masking rule): `** ****** J 00 8`.
 *
 * @see https://www.gesetze-im-internet.de/vkvv/__2.html VKVV § 2 (Aufbau der Versicherungsnummer)
 * @see https://www.deutsche-rentenversicherung.de/SharedDocs/Glossareintraege/DE/V/versicherungsnummer.html DRV
 *      glossary (display form `65 170839 J 08 8`)
 * @see docs/sources/DE.md "Rentenversicherungsnummer"
 */
final class PensionInsuranceNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'German pension insurance number';

    private const array WEIGHTS = [2, 1, 2, 5, 7, 1, 2, 1, 2, 1, 2, 1];

    private const string DIGITS = '0123456789';

    /**
     * @return non-empty-string the Bereichsnummer of the issuing carrier, positions 1-2
     */
    public function areaNumber(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
    }

    /**
     * @return non-empty-string positions 3-8 as issued, day, month and the last two digits of the year (`DDMMYY`)
     */
    public function birthDateField(): string
    {
        return Text::nonEmpty(\substr($this->value, 2, 6));
    }

    /**
     * @return non-empty-string the initial of the birth name at the time of issue, `A`-`Z`
     */
    public function birthNameInitial(): string
    {
        return Text::nonEmpty(\substr($this->value, 8, 1));
    }

    /**
     * @return non-empty-string positions 10-11: 00-49 male, 50-99 female, no entry or "divers"
     */
    public function serialNumber(): string
    {
        return Text::nonEmpty(\substr($this->value, 9, 2));
    }

    /**
     * @return non-empty-string e.g. "65 230701 J 00 8"
     */
    public function formatted(): string
    {
        return $this->layout(Text::nonEmpty($this->value));
    }

    #[Override]
    public function masked(): string
    {
        return $this->layout(Mask::keepLastCharacters(Text::nonEmpty($this->value), 4));
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 12, 12)->parse($value, self::DOCUMENT);
        if (
            \str_contains(self::DIGITS, $number[8])
            || \strspn($number, self::DIGITS) !== 8
            || \strspn($number, self::DIGITS, 9) !== 3
        ) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        if ($this->checkDigit($number) !== (int) $number[11]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $number;
    }

    /**
     * @param non-empty-string $number
     *
     * @return non-empty-string
     */
    private function layout(string $number): string
    {
        return \sprintf(
            '%s %s %s %s %s',
            \substr($number, 0, 2),
            \substr($number, 2, 6),
            \substr($number, 8, 1),
            \substr($number, 9, 2),
            \substr($number, 11),
        );
    }

    private function checkDigit(string $number): int
    {
        $letter = \ord($number[8]) - \ord('A') + 1;
        $digits = \sprintf('%s%02d%s', \substr($number, 0, 8), $letter, \substr($number, 9, -1));

        $sum = 0;
        foreach (self::WEIGHTS as $index => $weight) {
            $product = (int) $digits[$index] * $weight;
            $sum += \intdiv($product, 10) + $product % 10;
        }

        return $sum % 10;
    }
}
