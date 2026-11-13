<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use MSSTC4PHP\ValueObject\Documents\Contract\Subdivision;
use Override;

enum TestSubdivision: string implements Subdivision
{
    case Nsw = 'AU-NSW';
    case Vic = 'AU-VIC';

    #[Override]
    public function isoCode(): string
    {
        return $this->value;
    }
}
