<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

use MSSTC4PHP\ValueObject\Documents\Sex;

interface HasSex
{
    public function sex(): Sex;
}
