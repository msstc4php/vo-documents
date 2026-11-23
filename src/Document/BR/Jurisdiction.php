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
    case AC = 'BR-AC';
    case AL = 'BR-AL';
    case AM = 'BR-AM';
    case AP = 'BR-AP';
    case BA = 'BR-BA';
    case CE = 'BR-CE';
    case DF = 'BR-DF';
    case ES = 'BR-ES';
    case GO = 'BR-GO';
    case MA = 'BR-MA';
    case MG = 'BR-MG';
    case MS = 'BR-MS';
    case MT = 'BR-MT';
    case PA = 'BR-PA';
    case PB = 'BR-PB';
    case PE = 'BR-PE';
    case PI = 'BR-PI';
    case PR = 'BR-PR';
    case RJ = 'BR-RJ';
    case RN = 'BR-RN';
    case RO = 'BR-RO';
    case RR = 'BR-RR';
    case RS = 'BR-RS';
    case SC = 'BR-SC';
    case SE = 'BR-SE';
    case SP = 'BR-SP';
    case TO = 'BR-TO';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
