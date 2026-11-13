<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\FR;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Letters of the civil status: `A-Z`, the diacritic letters `à â ä é è ê ë ï î ô ö ù û ü ÿ ç` (both cases) and the
 * ligatures `æ` and `œ`; "tout autre signe diacritique … ne peut être retenu" (`ñ`, `ł`, `ß` are refused). Words are
 * joined by a hyphen, an apostrophe or a space. See docs/sources/FR.md "Names".
 *
 * @see https://www.legifrance.gouv.fr/circulaire/id/38565 Circulaire du 23 juillet 2014 relative à l'état civil
 *      (JUSC1412888C), § 1
 */
abstract class AbstractFrenchNamePart extends AbstractLatinNamePart
{
    #[Override]
    final protected function letters(): string
    {
        return 'A-Za-zÀÂÄÇÈÉÊËÎÏÔÖÙÛÜŸàâäçèéêëîïôöùûüÿÆæŒœ';
    }
}
