<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\ES;

/**
 * The NIF/NIE check letter: the number divided by 23, the remainder replaced by the letter of the Interior ministry's
 * table.
 *
 * @see https://www.interior.gob.es/opencms/es/servicios-al-ciudadano/tramites-y-gestiones/dni/calculo-del-digito-de-control-del-nif-nie/
 *      Ministerio del Interior, "Cálculo del dígito de control del NIF/NIE"
 * @see docs/sources/ES.md "DNI"
 *
 * @internal
 */
final class ControlLetter
{
    private const string LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    /**
     * @param string $digits at most eight ASCII digits
     *
     * @return non-empty-string one upper-case letter
     */
    public static function of(string $digits): string
    {
        return self::LETTERS[(int) $digits % 23];
    }
}
