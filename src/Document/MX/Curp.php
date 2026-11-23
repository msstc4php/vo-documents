<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use DateTimeImmutable;
use DateTimeZone;
use MSSTC4PHP\ValueObject\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Contract\HasBirthDate;
use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Clave Única de Registro de Población, 18 characters (RENAPO Instructivo Normativo, DOF 18-06-2018, numeral Tercero):
 * four letters of the name, the birth date `AAMMDD`, the sex, two letters of the birth state, three consonants, the
 * homonymy-and-century character and the check digit. Positions 1-4 and 12-16 are letters only ("Alfabética"); a
 * substituted `X` (Ñ, an "inconveniente" word, no internal vowel, one or no surname: Reglas 2021, 5.1-5.2) is an
 * ordinary letter there and is accepted. Character 17 is `0-9` for a birth up to 1999-12-31 and `A-J` from 2000-01-01,
 * so the birth date is complete and must exist (`30` February and 1900-02-29 are refused). The Instructivo gives "0-9 …
 * hasta el 31 de diciembre de 1999" with no lower bound, so a person born before 1900 reads as 19YY. The sex is `H` or
 * `M`, or `X`, which RENAPO assigns to non-binary persons (INE DERFE, 2023). Format only for the check digit: RENAPO
 * names "un algoritmo" but does not publish it. No display form is published; `masked()` keeps the last four characters
 * (no official masking rule): `**************RN04`.
 *
 * @see https://dof.gob.mx/nota_detalle_popup.php?codigo=5526717 Instructivo Normativo para la asignación de la CURP
 *      (DOF 18-06-2018), modified DOF 18-10-2021 (codigo=5632965)
 * @see https://www.gob.mx/cms/uploads/attachment/file/681698/reglas_para_la_ejecucion_de_los_procedimientos_asignacion_de_la_curp.pdf
 *      Reglas para la ejecución de los procedimientos para la asignación de la CURP (2021-10-22), 5.1-5.3
 * @see docs/sources/MX.md "CURP"
 */
final class Curp extends AbstractStringValueObject implements HasBirthDate, HasSex, Maskable
{
    public const int MAX_LENGTH = 18;

    private const string DOCUMENT = 'Mexican CURP';

    private const string SHAPE = '/[A-Z]{4}[0-9]{6}[HMX][A-Z]{5}[0-9A-J][0-9]/';

    #[Override]
    public function birthDate(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->isoDate($this->value), new DateTimeZone('UTC'));
    }

    #[Override]
    public function sex(): Sex
    {
        return match ($this->value[10]) {
            'H' => Sex::Male,
            'M' => Sex::Female,
            default => Sex::Unspecified,
        };
    }

    /**
     * @return non-empty-string the two letters of the RENAPO catalogue of birth states (positions 12-13), e.g. "DF";
     *                          not an ISO 3166-2 code, and the catalogue includes a code for a birth abroad
     */
    public function birthStateCode(): string
    {
        return Text::nonEmpty(\substr($this->value, 11, 2));
    }

    #[Override]
    public function masked(): string
    {
        return Mask::keepLastCharacters(Text::nonEmpty($this->value), 4);
    }

    #[Override]
    protected function parse(string $value): string
    {
        $code = new NumberFormat('0-9A-Z', 18, 18)->parse($value, self::DOCUMENT);
        if (\preg_match(self::SHAPE, $code) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid character).', self::DOCUMENT));
        }

        [$year, $month, $day] = \explode('-', $this->isoDate($code));
        if (!\checkdate((int) $month, (int) $day, (int) $year)) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', self::DOCUMENT));
        }

        return $code;
    }

    /**
     * Character 17: a digit up to 1999, a letter from 2000 (Instructivo, numeral Tercero).
     *
     * @return non-empty-string `YYYY-MM-DD`, not validated
     */
    private function isoDate(string $code): string
    {
        $century = \ctype_digit($code[16]) ? '19' : '20';

        return $century . \substr($code, 4, 2) . '-' . \substr($code, 6, 2) . '-' . \substr($code, 8, 2);
    }
}
