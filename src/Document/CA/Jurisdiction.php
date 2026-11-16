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
    case Ab = 'CA-AB';
    case Bc = 'CA-BC';
    case Mb = 'CA-MB';
    case Nb = 'CA-NB';
    case Nl = 'CA-NL';
    case Ns = 'CA-NS';
    case Nt = 'CA-NT';
    case Nu = 'CA-NU';
    case On = 'CA-ON';
    case Pe = 'CA-PE';
    case Qc = 'CA-QC';
    case Sk = 'CA-SK';
    case Yt = 'CA-YT';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
