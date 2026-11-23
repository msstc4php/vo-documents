<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\IN;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Aadhaar number, 12 digits (UIDAI, "A UID Numbering Scheme", 2010: "12 decimal digits will be sufficient") whose last
 * digit is the Verhoeff check digit ("We recommend the Verhoeff scheme"). The paper words this as a recommendation and
 * no later UIDAI text restating it was found. Its remarks that a first digit of 0 or 1 "could be reserved" are not a
 * rule and are not enforced. The 16-digit Virtual ID is a different identifier and is refused by length. `masked()` is
 * UIDAI's masked Aadhaar, the first eight digits replaced and the last four visible: `XXXX XXXX 1234`. No display form
 * is quoted, so there is no `formatted()`.
 *
 * @see https://web.archive.org/web/20100704211109/www.uidai.gov.in/documents/A_UID_Numbering_Scheme.pdf UIDAI
 * @see https://web.archive.org/web/20220811180652/https://www.uidai.gov.in/283-faqs/aadhaar-online-services/e-aadhaar/1887-what-is-masked-aadhaar.html UIDAI FAQ
 * @see docs/sources/IN.md "Aadhaar number"
 */
final class AadhaarNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 12;

    private const string DOCUMENT = 'Aadhaar number';

    #[Override]
    public function masked(): string
    {
        return 'XXXX XXXX ' . \substr($this->value, 8);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 12, self::DOCUMENT);

        if (!Checksum::verhoeff($digits)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return Text::nonEmpty($digits);
    }
}
