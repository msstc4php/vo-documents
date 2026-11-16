<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\MX;

use Override;

/**
 * One surname (`Hernández`, `de la Cruz`, `Riva Palacio`, `López-Castillo`, `D/Amico`), a particle in the case given.
 * RENAPO lists no letter set, so the package's Latin default applies; the characters between words are those of
 * {@see AbstractMexicanNamePart}. See docs/sources/MX.md "Names".
 *
 * @see https://www.gob.mx/cms/uploads/attachment/file/681698/reglas_para_la_ejecucion_de_los_procedimientos_asignacion_de_la_curp.pdf
 *      Reglas para la ejecución de los procedimientos para la asignación de la CURP (2021), 5.1
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class FamilyName extends AbstractMexicanNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Mexican family name';
    }
}
