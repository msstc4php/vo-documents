<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\IT;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * One or several given names (`Maria Grazia`, `Gian-Luca`), words of Latin letters joined by a hyphen, an apostrophe
 * or a space. A comma between given names (DPR 396/2000 art. 35 c. 2) is not accepted; see docs/sources/IT.md
 * "Names".
 *
 * @see https://www.normattiva.it/uri-res/N2Ls?urn:nir:presidente.repubblica:decreto:2000-11-03;396~art35 DPR
 *      3 novembre 2000, n. 396, art. 35 (given names)
 * @see https://www.icao.int/publications/Documents/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3, 8th ed. (2021), §6.A
 */
final class GivenName extends AbstractLatinNamePart
{
    #[Override]
    protected function label(): string
    {
        return 'Italian given name';
    }
}
