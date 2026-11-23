<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\MX;

use Override;

/**
 * The given name or names (`María Guadalupe`, `José Ángel`, `Ma. Guadalupe`, `J. Ricardo`); several names are one
 * part, as RENAPO captures them. Characters as {@see AbstractMexicanNamePart}. See docs/sources/MX.md "Names".
 *
 * @see https://www.gob.mx/cms/uploads/attachment/file/681698/reglas_para_la_ejecucion_de_los_procedimientos_asignacion_de_la_curp.pdf
 *      Reglas para la ejecución de los procedimientos para la asignación de la CURP (2021), 5.1
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class GivenName extends AbstractMexicanNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Mexican given name';
    }
}
