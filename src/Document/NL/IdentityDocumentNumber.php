<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\NL;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Documentnummer of a Dutch passport, emergency passport, laissez-passer or Dutch identity card: nine characters,
 * positions 1-2 letters, 3-8 letters or digits, 9 a digit; the letter `O` is never used. The digit `0` is accepted:
 * the 2014 model may hold it and only the 2021 and 2024 models leave it out. The number has no check digit of its
 * own; the specimen is `SPECI2024`. `masked()` keeps the last four characters (no official masking rule): `*****2024`.
 *
 * @see https://www.rvig.nl/sites/default/files/2023-02/Kenmerkenbrochure%20paspoorten%20en%20identiteitskaarten%20model%202014%20NL.pdf
 *      RvIG, Kenmerken paspoorten en identiteitskaarten model 2014, kenmerk 5
 * @see https://rvig.nl/sites/default/files/2024-09/Kenmerkenbrochure%20paspoort%20model%202024.pdf RvIG, Kenmerken
 *      paspoort model 2024, kenmerk 5
 * @see https://www.rijksoverheid.nl/themas/migratie-en-reizen/paspoort-en-identiteitskaart/vraag-en-antwoord/paspoort-en-identiteitskaart/bevat-mijn-paspoortnummer-het-cijfer-0-of-de-letter-o
 *      Rijksoverheid, "Bevat mijn paspoortnummer het cijfer 0 of de letter O?"
 * @see docs/sources/NL.md "Passport and identity card number"
 */
final class IdentityDocumentNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Dutch passport or identity card number';

    private const string LETTERS = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('A-NP-Z0-9', 9, 9)->parse($value, self::DOCUMENT);
        if (\strspn($number, self::LETTERS, 0, 2) !== 2 || \strspn($number, '0123456789', 8) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
