<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\MX;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * The given name or names (`María Guadalupe`, `José Ángel`): words of Latin letters joined by a hyphen, an apostrophe
 * or a space; several names are one part, as RENAPO captures them. See docs/sources/MX.md "Names".
 *
 * @see https://www.gob.mx/cms/uploads/attachment/file/681698/reglas_para_la_ejecucion_de_los_procedimientos_asignacion_de_la_curp.pdf
 *      Reglas para la ejecución de los procedimientos para la asignación de la CURP (2021), 5.1
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class GivenName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Mexican given name';
    }
}
