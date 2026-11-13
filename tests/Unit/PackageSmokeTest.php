<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit;

use MSSTC4PHP\ValueObject\Documents\Document\RU\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\RU\Inn;
use MSSTC4PHP\ValueObject\Documents\Document\RU\InternalPassportNumber;
use MSSTC4PHP\ValueObject\Documents\Document\RU\InternationalPassportNumber;
use MSSTC4PHP\ValueObject\Documents\Document\RU\OmsPolicyNumber;
use MSSTC4PHP\ValueObject\Documents\Document\RU\PassportIssuingUnitCode;
use MSSTC4PHP\ValueObject\Documents\Document\RU\Snils;
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
        yield 'RU SNILS' => [Snils::class];
        yield 'RU INN' => [Inn::class];
        yield 'RU internal passport number' => [InternalPassportNumber::class];
        yield 'RU passport issuing unit code' => [PassportIssuingUnitCode::class];
        yield 'RU international passport number' => [InternationalPassportNumber::class];
        yield 'RU driving licence number' => [DriverLicenceNumber::class];
        yield 'RU OMS policy number' => [OmsPolicyNumber::class];
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
