<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\US;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use Override;

/**
 * The 50 states and the District of Columbia, ISO 3166-2:US; the outlying areas are out of scope.
 *
 * @see https://www.iso.org/obp/ui/#iso:code:3166:US ISO 3166-2:US
 */
enum Jurisdiction: string implements Subdivision
{
    case AK = 'US-AK';
    case AL = 'US-AL';
    case AR = 'US-AR';
    case AZ = 'US-AZ';
    case CA = 'US-CA';
    case CO = 'US-CO';
    case CT = 'US-CT';
    case DC = 'US-DC';
    case DE = 'US-DE';
    case FL = 'US-FL';
    case GA = 'US-GA';
    case HI = 'US-HI';
    case IA = 'US-IA';
    case ID = 'US-ID';
    case IL = 'US-IL';
    case IN = 'US-IN';
    case KS = 'US-KS';
    case KY = 'US-KY';
    case LA = 'US-LA';
    case MA = 'US-MA';
    case MD = 'US-MD';
    case ME = 'US-ME';
    case MI = 'US-MI';
    case MN = 'US-MN';
    case MO = 'US-MO';
    case MS = 'US-MS';
    case MT = 'US-MT';
    case NC = 'US-NC';
    case ND = 'US-ND';
    case NE = 'US-NE';
    case NH = 'US-NH';
    case NJ = 'US-NJ';
    case NM = 'US-NM';
    case NV = 'US-NV';
    case NY = 'US-NY';
    case OH = 'US-OH';
    case OK = 'US-OK';
    case OR = 'US-OR';
    case PA = 'US-PA';
    case RI = 'US-RI';
    case SC = 'US-SC';
    case SD = 'US-SD';
    case TN = 'US-TN';
    case TX = 'US-TX';
    case UT = 'US-UT';
    case VA = 'US-VA';
    case VT = 'US-VT';
    case WA = 'US-WA';
    case WI = 'US-WI';
    case WV = 'US-WV';
    case WY = 'US-WY';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
