<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\DriverLicenceCategory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "Driving licence" (MVD Order 365, section 9 print order).
 */
#[CoversNothing]
final class DriverLicenceCategoryTest extends TestCase
{
    public function testListsTheCategoriesInTheOrderTheLicencePrintsThem(): void
    {
        self::assertSame(
            ['A', 'A1', 'B', 'B1', 'C', 'C1', 'D', 'D1', 'BE', 'CE', 'C1E', 'DE', 'D1E', 'M', 'Tm', 'Tb'],
            \array_map(
                static fn (DriverLicenceCategory $category): string => $category->value,
                DriverLicenceCategory::cases(),
            ),
        );
    }

    public function testRefusesCyrillicLookAlikes(): void
    {
        self::assertNull(DriverLicenceCategory::tryFrom('В'));
        self::assertNull(DriverLicenceCategory::tryFrom('TM'));
    }
}
