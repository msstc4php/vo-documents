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
    case Ak = 'US-AK';
    case Al = 'US-AL';
    case Ar = 'US-AR';
    case Az = 'US-AZ';
    case Ca = 'US-CA';
    case Co = 'US-CO';
    case Ct = 'US-CT';
    case Dc = 'US-DC';
    case De = 'US-DE';
    case Fl = 'US-FL';
    case Ga = 'US-GA';
    case Hi = 'US-HI';
    case Ia = 'US-IA';
    case Id = 'US-ID';
    case Il = 'US-IL';
    case In = 'US-IN';
    case Ks = 'US-KS';
    case Ky = 'US-KY';
    case La = 'US-LA';
    case Ma = 'US-MA';
    case Md = 'US-MD';
    case Me = 'US-ME';
    case Mi = 'US-MI';
    case Mn = 'US-MN';
    case Mo = 'US-MO';
    case Ms = 'US-MS';
    case Mt = 'US-MT';
    case Nc = 'US-NC';
    case Nd = 'US-ND';
    case Ne = 'US-NE';
    case Nh = 'US-NH';
    case Nj = 'US-NJ';
    case Nm = 'US-NM';
    case Nv = 'US-NV';
    case Ny = 'US-NY';
    case Oh = 'US-OH';
    case Ok = 'US-OK';
    case Or = 'US-OR';
    case Pa = 'US-PA';
    case Ri = 'US-RI';
    case Sc = 'US-SC';
    case Sd = 'US-SD';
    case Tn = 'US-TN';
    case Tx = 'US-TX';
    case Ut = 'US-UT';
    case Va = 'US-VA';
    case Vt = 'US-VT';
    case Wa = 'US-WA';
    case Wi = 'US-WI';
    case Wv = 'US-WV';
    case Wy = 'US-WY';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
