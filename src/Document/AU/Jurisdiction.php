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
    case ACT = 'AU-ACT';
    case NSW = 'AU-NSW';
    case NT = 'AU-NT';
    case QLD = 'AU-QLD';
    case SA = 'AU-SA';
    case TAS = 'AU-TAS';
    case VIC = 'AU-VIC';
    case WA = 'AU-WA';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
