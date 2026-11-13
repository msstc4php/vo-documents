<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\ES;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número de la Seguridad Social / número de afiliación (NUSS, NAF): the two-digit "Provincia" (01-56) and the
 * ten-digit "Número" of the TGSS affiliation message. Format only: the check digits are not verified because no
 * official algorithm is published. `masked()` keeps the last four digits (no official masking rule): `********5678`.
 *
 * @see https://www.seg-social.es/descarga/es/43234 TGSS, Sistema RED, "Mensaje de Afiliación … Versión 4.0.0",
 *      segment TRA, data 710-730
 * @see https://www.boe.es/buscar/pdf/1997/BOE-A-1997-27151-consolidado.pdf Resolución de 1 de diciembre de 1997
 * @see docs/sources/ES.md "Número de la Seguridad Social"
 */
final class SocialSecurityNumber extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Spanish social security number';

    private const int LAST_PROVINCE = 56;

    /**
     * @return non-empty-string the "Provincia" field, two digits (`28`); the TGSS publishes no code table here
     */
    public function provinceCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 0, 2));
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
        $province = (int) \substr($number, 0, 2);
        if ($province < 1 || $province > self::LAST_PROVINCE) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid province code).', self::DOCUMENT));
        }

        return $number;
    }
}
