<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture;

use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

final class PackageString extends AbstractStringValueObject
{
    #[Override]
    protected function parse(string $value): string
    {
        return $value;
    }
}
