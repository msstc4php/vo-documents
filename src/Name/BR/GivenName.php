<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\BR;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * The given name or names ("prenome"), all of them as one part (`Maria da Conceição`, `João Pedro`); same characters
 * as {@see FamilyName}. See docs/sources/BR.md "Names".
 *
 * @see https://atos.cnj.jus.br/atos/detalhar/5243 CNJ, Código Nacional de Normas (Provimento n. 149/2023),
 *      art. 515-B
 */
final class GivenName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Brazilian given name';
    }
}
