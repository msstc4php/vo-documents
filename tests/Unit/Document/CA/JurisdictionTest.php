<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Jurisdiction::class)]
final class JurisdictionTest extends TestCase
{
    public function testCasesAreTheThirteenProvincesAndTerritoriesInIsoOrder(): void
    {
        $codes = \array_map(
            static fn (Jurisdiction $jurisdiction): string => $jurisdiction->isoCode(),
            Jurisdiction::cases(),
        );

        self::assertSame(
            [
                'CA-AB', 'CA-BC', 'CA-MB', 'CA-NB', 'CA-NL', 'CA-NS', 'CA-NT', 'CA-NU', 'CA-ON', 'CA-PE', 'CA-QC',
                'CA-SK', 'CA-YT',
            ],
            $codes,
        );
    }

    public function testIsoCodeIsTheBackingValue(): void
    {
        self::assertSame(Jurisdiction::QC, Jurisdiction::from('CA-QC'));
        self::assertSame('CA-YT', Jurisdiction::YT->isoCode());
    }
}
