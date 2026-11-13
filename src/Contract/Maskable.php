<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

interface Maskable
{
    /**
     * @return non-empty-string
     */
    public function masked(): string;
}
