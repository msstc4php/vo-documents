<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use Override;

/**
 * The 10 provinces and 3 territories of Canada, ISO 3166-2:CA.
 *
 * @see https://www.iso.org/obp/ui/#iso:code:3166:CA ISO 3166-2:CA
 */
enum Jurisdiction: string implements Subdivision
{
    case AB = 'CA-AB';
    case BC = 'CA-BC';
    case MB = 'CA-MB';
    case NB = 'CA-NB';
    case NL = 'CA-NL';
    case NS = 'CA-NS';
    case NT = 'CA-NT';
    case NU = 'CA-NU';
    case ON = 'CA-ON';
    case PE = 'CA-PE';
    case QC = 'CA-QC';
    case SK = 'CA-SK';
    case YT = 'CA-YT';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
