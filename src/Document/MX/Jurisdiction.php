<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use Override;

/**
 * The 31 states and Mexico City (MX-CMX), the 32 federal entities of ISO 3166-2:MX.
 *
 * @see https://www.iso.org/obp/ui/#iso:code:3166:MX ISO 3166-2:MX
 */
enum Jurisdiction: string implements Subdivision
{
    case AGU = 'MX-AGU';
    case BCN = 'MX-BCN';
    case BCS = 'MX-BCS';
    case CAM = 'MX-CAM';
    case CHH = 'MX-CHH';
    case CHP = 'MX-CHP';
    case CMX = 'MX-CMX';
    case COA = 'MX-COA';
    case COL = 'MX-COL';
    case DUR = 'MX-DUR';
    case GRO = 'MX-GRO';
    case GUA = 'MX-GUA';
    case HID = 'MX-HID';
    case JAL = 'MX-JAL';
    case MEX = 'MX-MEX';
    case MIC = 'MX-MIC';
    case MOR = 'MX-MOR';
    case NAY = 'MX-NAY';
    case NLE = 'MX-NLE';
    case OAX = 'MX-OAX';
    case PUE = 'MX-PUE';
    case QUE = 'MX-QUE';
    case ROO = 'MX-ROO';
    case SIN = 'MX-SIN';
    case SLP = 'MX-SLP';
    case SON = 'MX-SON';
    case TAB = 'MX-TAB';
    case TAM = 'MX-TAM';
    case TLA = 'MX-TLA';
    case VER = 'MX-VER';
    case YUC = 'MX-YUC';
    case ZAC = 'MX-ZAC';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
