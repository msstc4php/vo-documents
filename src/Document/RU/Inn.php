<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * ИНН of an individual: 12 digits `NNYYXXXXXXCC`. The two control digits are each a weighted sum mod 11 mod 10
 * (weights 7,2,4,10,3,5,9,4,6,8 over digits 1-10, then 3,7,2,4,10,3,5,9,4,6,8 over digits 1-11). The FNS order states
 * only that CC is "рассчитанное по алгоритму, определенному Федеральной налоговой службой" and does not publish the
 * weights (see RU.md). Twelve zeros, the "no INN" filler of the FNS formats, are refused (SFR check
 * ВС.ЕФС-СЗВ-ТД.1.14). `masked()` keeps the last four digits (no official masking rule): `********2259`.
 *
 * @see http://publication.pravo.gov.ru/document/0001202510090011 FNS Order of 26.06.2025 No. ЕД-7-14/559@, §2-§3
 * @see http://publication.pravo.gov.ru/document/0001202512190014 SFR Order of 17.11.2025 No. 1463, ВС.ЕФС-СЗВ-ТД.1.14
 * @see docs/sources/RU.md "INN"
 */
final class Inn extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'INN';

    /**
     * NN: the code of the FNS directorate of the constituent entity where the INN was assigned (FNS Order
     * ЕД-7-14/559@ §3). In an INN assigned before 2026 (Order ММВ-7-6/435@) the first four digits are the code of the
     * assigning tax office, and its first two digits are the code of the constituent entity in the FNS code list.
     * This is an FNS code, not an ISO 3166-2 code.
     *
     * @return non-empty-string two digits
     */
    public function regionCode(): string
    {
        return Digits::nonEmpty(\substr($this->value, 0, 2));
    }

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return Digits::nonEmpty($this->value);
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits($this->formatted(), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $digits = Digits::exactly($value, 12, self::DOCUMENT);

        if ($digits === '000000000000') {
            throw new InvalidDocumentNumberException('Invalid INN (zeros only).');
        }

        $first = Checksum::weightedMod(\substr($digits, 0, 10), [7, 2, 4, 10, 3, 5, 9, 4, 6, 8], 11) % 10;
        $second = Checksum::weightedMod(\substr($digits, 0, 11), [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8], 11) % 10;

        if (\substr($digits, 10) !== $first . $second) {
            throw new InvalidDocumentNumberException('Invalid INN checksum.');
        }

        return $digits;
    }
}
