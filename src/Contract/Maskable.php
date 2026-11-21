<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

use MSSTC4PHP\ValueObject\Contract\Maskable as BaseMaskable;
use Override;

interface Maskable extends BaseMaskable
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function masked(): string;
}
