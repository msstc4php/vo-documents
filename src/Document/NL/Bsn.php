<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\NL;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Burgerservicenummer: nine digits passing the elfproef, `9·s0 + 8·s1 + … + 2·s7 − 1·s8` divisible by 11. An
 * eight-digit number is read with a leading zero, as the Logisch Ontwerp BSN says, and stored with nine digits. No
 * data is encoded ("informatieloos"). `masked()` keeps the last four digits (no official masking rule):
 * `*****0068`.
 *
 * @see https://rvig.nl/sites/default/files/2025-11/Logisch%20Ontwerp%20BSN%202025.Q4.pdf Logisch Ontwerp BSN
 *      2025.Q4 (Regeling burgerservicenummer, art. 3), § 3.2.1 and § I.2.3, footnote 22
 * @see docs/sources/NL.md "BSN"
 */
final class Bsn extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Dutch BSN';

    private const array WEIGHTS = [9, 8, 7, 6, 5, 4, 3, 2, -1];

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = \str_pad(new NumberFormat('0-9', 8, 9)->parse($value, self::DOCUMENT), 9, '0', \STR_PAD_LEFT);
        if (Checksum::weightedMod($number, self::WEIGHTS, 11) !== 0) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $number;
    }
}
