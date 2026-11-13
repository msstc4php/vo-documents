<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ES;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * One surname (`García`, `de la Fuente`, `Muñoz`, `d'Ors`): words of Latin letters joined by a hyphen, an apostrophe
 * or a space; a particle keeps its case. No Spanish text lists the letters of a civil-status name, so the package's
 * Latin default applies; see docs/sources/ES.md "Names".
 *
 * @see https://www.boe.es/buscar/pdf/2011/BOE-A-2011-12628-consolidado.pdf Ley 20/2011 del Registro Civil, arts. 49,
 *      53, 56
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class FamilyName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Spanish family name';
    }
}
