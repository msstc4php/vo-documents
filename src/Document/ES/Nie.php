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
 * Número de Identidad de Extranjero: `X`, `Y` or `Z`, seven digits and a check letter, `X1234567L`. The letter is
 * verified with the DNI algorithm after the initial letter is replaced by `0`, `1` or `2`. A number assigned before
 * Orden INT/2058/2008 (`X`, eight digits and a letter, `X12345678Z`) stays valid; no check algorithm is published for
 * it, so its letter is not verified (format only). It is also the number of a driving licence issued to an NIE holder.
 * `masked()` keeps the last four characters (no official masking rule): `*****567L`, `******678Z`.
 *
 * @see https://www.interior.gob.es/opencms/es/servicios-al-ciudadano/tramites-y-gestiones/dni/calculo-del-digito-de-control-del-nif-nie/
 *      Ministerio del Interior, "Cálculo del dígito de control del NIF/NIE"
 * @see https://www.boe.es/boe/dias/2008/07/15/pdfs/A30880-30881.pdf Orden INT/2058/2008, apartado único
 * @see docs/sources/ES.md "NIE"
 */
final class Nie extends AbstractStringValueObject implements Maskable
{
    private const string DOCUMENT = 'Spanish NIE';

    private const string INITIALS = 'XYZ';

    private const int LEGACY_LENGTH = 10;

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $number = new NumberFormat('0-9A-Z', 9, self::LEGACY_LENGTH)->parse($value, self::DOCUMENT);
        if (\strlen($number) === self::LEGACY_LENGTH) {
            return $this->parseLegacy($number);
        }

        $initial = \strpos(self::INITIALS, $number[0]);
        if ($initial === false || \strspn($number, '0123456789', 1) !== 7) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        if (ControlLetter::of($initial . \substr($number, 1, 7)) !== $number[8]) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s checksum.', self::DOCUMENT));
        }

        return $number;
    }

    /**
     * @param non-empty-string $number
     *
     * @return non-empty-string
     */
    private function parseLegacy(string $number): string
    {
        if ($number[0] !== 'X' || \strspn($number, '0123456789', 1) !== 8 || \ctype_digit($number[9])) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        return $number;
    }
}
