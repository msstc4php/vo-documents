<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use Override;

/**
 * The 26 states and the Distrito Federal (BR-DF), the 27 federative units of ISO 3166-2:BR.
 *
 * @see https://www.iso.org/obp/ui/#iso:code:3166:BR ISO 3166-2:BR
 */
enum Jurisdiction: string implements Subdivision
{
    case Ac = 'BR-AC';
    case Al = 'BR-AL';
    case Am = 'BR-AM';
    case Ap = 'BR-AP';
    case Ba = 'BR-BA';
    case Ce = 'BR-CE';
    case Df = 'BR-DF';
    case Es = 'BR-ES';
    case Go = 'BR-GO';
    case Ma = 'BR-MA';
    case Mg = 'BR-MG';
    case Ms = 'BR-MS';
    case Mt = 'BR-MT';
    case Pa = 'BR-PA';
    case Pb = 'BR-PB';
    case Pe = 'BR-PE';
    case Pi = 'BR-PI';
    case Pr = 'BR-PR';
    case Rj = 'BR-RJ';
    case Rn = 'BR-RN';
    case Ro = 'BR-RO';
    case Rr = 'BR-RR';
    case Rs = 'BR-RS';
    case Sc = 'BR-SC';
    case Se = 'BR-SE';
    case Sp = 'BR-SP';
    case To = 'BR-TO';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
