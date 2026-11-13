<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use Override;

/**
 * The Australian states and territories, ISO 3166-2:AU.
 *
 * @see https://www.iso.org/obp/ui/#iso:code:3166:AU ISO 3166-2:AU
 */
enum Jurisdiction: string implements Subdivision
{
    case Act = 'AU-ACT';
    case Nsw = 'AU-NSW';
    case Nt = 'AU-NT';
    case Qld = 'AU-QLD';
    case Sa = 'AU-SA';
    case Tas = 'AU-TAS';
    case Vic = 'AU-VIC';
    case Wa = 'AU-WA';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
