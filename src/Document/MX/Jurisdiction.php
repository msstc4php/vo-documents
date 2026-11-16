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
    case Agu = 'MX-AGU';
    case Bcn = 'MX-BCN';
    case Bcs = 'MX-BCS';
    case Cam = 'MX-CAM';
    case Chh = 'MX-CHH';
    case Chp = 'MX-CHP';
    case Cmx = 'MX-CMX';
    case Coa = 'MX-COA';
    case Col = 'MX-COL';
    case Dur = 'MX-DUR';
    case Gro = 'MX-GRO';
    case Gua = 'MX-GUA';
    case Hid = 'MX-HID';
    case Jal = 'MX-JAL';
    case Mex = 'MX-MEX';
    case Mic = 'MX-MIC';
    case Mor = 'MX-MOR';
    case Nay = 'MX-NAY';
    case Nle = 'MX-NLE';
    case Oax = 'MX-OAX';
    case Pue = 'MX-PUE';
    case Que = 'MX-QUE';
    case Roo = 'MX-ROO';
    case Sin = 'MX-SIN';
    case Slp = 'MX-SLP';
    case Son = 'MX-SON';
    case Tab = 'MX-TAB';
    case Tam = 'MX-TAM';
    case Tla = 'MX-TLA';
    case Ver = 'MX-VER';
    case Yuc = 'MX-YUC';
    case Zac = 'MX-ZAC';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
