<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Contract;

interface IssuedBySubdivision
{
    public function jurisdiction(): Subdivision;
}
