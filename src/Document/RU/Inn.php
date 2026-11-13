<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * ИНН of an individual: 12 digits `NNYYXXXXXXCC`. Format only: the check digits are not verified because no official
 * algorithm is published (the FNS order says only that CC is "рассчитанное по алгоритму, определенному Федеральной
 * налоговой службой", see RU.md). Twelve zeros, the "no INN" filler of the FNS formats, are refused (SFR check
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
     * The first two digits: the region code of the FNS territorial body (since 2026 "код управления ФНС России по
     * субъекту", FNS Order ЕД-7-14/559@ §3; before, the first two digits of the assigning tax office's code). An FNS
     * code, not an ISO 3166-2 code.
     *
     * @return non-empty-string two digits
     */
    public function taxAuthorityRegionCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
    }

    /**
     * @return non-empty-string
     */
    public function formatted(): string
    {
        return Text::nonEmpty($this->value);
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
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (zeros only).', self::DOCUMENT));
        }

        return $digits;
    }
}
