<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonName;
use Override;

/**
 * @extends AbstractRequiredLatinPersonName<TestFamily, TestGiven>
 */
final readonly class TestPersonName extends AbstractRequiredLatinPersonName
{
    public function __construct(TestFamily $family, ?TestGiven $given, IcaoPersonName $latin)
    {
        parent::__construct($family, $given, $latin);
    }

    #[Override]
    protected function nationalSeparator(): string
    {
        return '/';
    }
}
