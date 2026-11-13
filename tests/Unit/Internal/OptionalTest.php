<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Internal\Optional;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\PackageString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Optional::class)]
final class OptionalTest extends TestCase
{
    public function testBothAbsentAreEqual(): void
    {
        self::assertTrue(Optional::equal(null, null));
    }

    public function testOneAbsentIsNotEqual(): void
    {
        self::assertFalse(Optional::equal(PackageString::from('a'), null));
        self::assertFalse(Optional::equal(null, PackageString::from('a')));
    }

    public function testComparesPresentValues(): void
    {
        self::assertTrue(Optional::equal(PackageString::from('a'), PackageString::from('a')));
        self::assertFalse(Optional::equal(PackageString::from('a'), PackageString::from('b')));
    }
}
