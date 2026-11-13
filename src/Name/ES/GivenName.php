<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\ES;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * The given name, simple or compound (`María José`, `Josep-Lluís`): words of Latin letters joined by a hyphen, an
 * apostrophe or a space. The art. 51 limit of two simple names is a naming rule and is not checked; see
 * docs/sources/ES.md "Names".
 *
 * @see https://www.boe.es/buscar/pdf/2011/BOE-A-2011-12628-consolidado.pdf Ley 20/2011 del Registro Civil, arts. 50,
 *      51
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class GivenName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Spanish given name';
    }
}
