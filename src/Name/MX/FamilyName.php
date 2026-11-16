<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\MX;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * One surname (`Hernández`, `de la Cruz`, `Riva Palacio`, `López-Castillo`): words of Latin letters joined by a hyphen,
 * an apostrophe or a space, a particle in the case given. RENAPO captures names "tal cual viene en el documento
 * probatorio" and lists no letter set, so the package's Latin default applies; the `/` and `.` of the RENAPO examples
 * have no ICAO spelling and are refused. See docs/sources/MX.md "Names".
 *
 * @see https://www.gob.mx/cms/uploads/attachment/file/681698/reglas_para_la_ejecucion_de_los_procedimientos_asignacion_de_la_curp.pdf
 *      Reglas para la ejecución de los procedimientos para la asignación de la CURP (2021), 5.1
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class FamilyName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Mexican family name';
    }
}
