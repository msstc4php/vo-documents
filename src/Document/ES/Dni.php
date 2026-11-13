<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\ES;

use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Número del Documento Nacional de Identidad with its "carácter de verificación" (the NIF of a Spanish national):
 * eight digits and a letter, `12345678Z`. The letter is verified: the number divided by 23, the remainder looked up
 * in the Interior ministry's table. A number below 10 000 000 is given with its leading zeros. It is also the number
 * of a driving licence issued to a DNI holder. `masked()` keeps the last four characters (no official masking rule):
 * `*****678Z`.
 *
 * @see https://www.interior.gob.es/opencms/es/servicios-al-ciudadano/tramites-y-gestiones/dni/ Ministerio del
 *      Interior, "Cálculo del dígito de control del NIF/NIE" (full address in docs/sources/ES.md)
 * @see https://www.boe.es/boe/dias/2025/04/02/pdfs/BOE-A-2025-6601.pdf Real Decreto 255/2025, arts. 2.2, 12 a)
 * @see https://www.boe.es/buscar/pdf/2007/BOE-A-2007-15984-consolidado.pdf Real Decreto 1065/2007, art. 19.1
 * @see docs/sources/ES.md "DNI"
 */
final class Dni extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Spanish DNI number';

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 9, 9)->parse($value, self::DOCUMENT);
        if (\strspn($number, '0123456789') !== 8) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        if (ControlLetter::of(\substr($number, 0, 8)) !== $number[8]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $number;
    }
}
