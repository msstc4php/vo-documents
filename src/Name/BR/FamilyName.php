<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\BR;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * The surnames ("sobrenome"), all of them as one part, in the case given: Portuguese names carry several surnames
 * with no fixed count, particles and an agnome included (`da Silva`, `dos Santos Oliveira`, `de Souza Júnior`). The
 * civil registry rules publish no letter set or punctuation, so the package's Latin default applies: Latin letters
 * (`ã`, `ç`, `ê`) joined by a space, a hyphen or an apostrophe (`D'Ávila`). See docs/sources/BR.md "Names".
 *
 * @see https://atos.cnj.jus.br/atos/detalhar/5243 CNJ, Código Nacional de Normas (Provimento n. 149/2023),
 *      art. 515-B
 */
final class FamilyName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Brazilian family name';
    }
}
