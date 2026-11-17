<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RequiredLatin;

use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RequiredLatin\AbstractFamilyGivenPersonNameCodec;
use Override;

/**
 * @extends AbstractFamilyGivenPersonNameCodec<TestPersonName>
 */
final readonly class TestPersonNameCodec extends AbstractFamilyGivenPersonNameCodec
{
    #[Override]
    public function valueObjectClass(): string
    {
        return TestPersonName::class;
    }

    #[Override]
    protected function shape(): string
    {
        return 'Expected a test person name payload with string "family", "given" and "latin", and no other keys.';
    }

    #[Override]
    protected function create(string $family, string $given, IcaoPersonName $latin): TestPersonName
    {
        return new TestPersonName(TestFamily::from($family), TestGiven::from($given), $latin);
    }
}
