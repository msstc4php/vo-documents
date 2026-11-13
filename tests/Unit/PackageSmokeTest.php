<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit;

use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\PackageString;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class PackageSmokeTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function packageValueObjects(): iterable
    {
        yield 'string value object' => [PackageString::class];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('packageValueObjects')]
    public function testCoreMapperTreatsAPackageStringValueObjectAsScalarBacked(string $class): void
    {
        self::assertTrue(new ValueObjectMapper()->isScalarBacked($class));
    }
}
