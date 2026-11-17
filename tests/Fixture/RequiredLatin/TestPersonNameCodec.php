<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractRequiredLatinPersonNameCodec;
use Override;

/**
 * @extends AbstractRequiredLatinPersonNameCodec<TestPersonName>
 */
final readonly class TestPersonNameCodec extends AbstractRequiredLatinPersonNameCodec
{
    #[Override]
    public function valueObjectClass(): string
    {
        return TestPersonName::class;
    }

    #[Override]
    protected function shape(): string
    {
        return 'Expected a test person name payload with string "family", string, null or missing "given", '
            . 'string "latin", and no other keys.';
    }

    #[Override]
    protected function create(string $family, ?string $given, IcaoPersonName $latin): TestPersonName
    {
        return new TestPersonName(TestFamily::from($family), $given === null ? null : TestGiven::from($given), $latin);
    }
}
