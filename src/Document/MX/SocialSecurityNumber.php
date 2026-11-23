<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número de Seguridad Social of the IMSS, eleven digits: "diez dígitos numéricos y dígito verificador" holding the
 * state where it is given, the year of incorporation, the birth year and a running number (IMSS norm 3000-001-007,
 * 5.57). The norm gives neither the positions of the fields nor the check-digit algorithm, so the number is format
 * only and exposes no fields. No display form is published; `masked()` keeps the last four digits (no official
 * masking rule): `*******8901`.
 *
 * @see http://reposipot.imss.gob.mx/normatividad/DNMR/Norma/3000-001-007.pdf IMSS, Norma 3000-001-007 (prestaciones
 *      económicas), 5.57 (read through the Internet Archive copy of 2018-08-27)
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/MX.md "NSS"
 */
final class SocialSecurityNumber extends AbstractStringValueObject implements Maskable
{
    public const int MAX_LENGTH = 11;

    private const string DOCUMENT = 'Mexican social security number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastDigits(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        return Digits::exactly($value, 11, self::DOCUMENT);
    }
}
