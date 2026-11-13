<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Jurisdiction::class)]
final class JurisdictionTest extends TestCase
{
    public function testCasesAreTheIso3166CodesInDeclaredOrder(): void
    {
        self::assertSame(
            ['AU-ACT', 'AU-NSW', 'AU-NT', 'AU-QLD', 'AU-SA', 'AU-TAS', 'AU-VIC', 'AU-WA'],
            \array_map(static fn (Jurisdiction $jurisdiction): string => $jurisdiction->isoCode(), Jurisdiction::cases()),
        );
    }

    public function testIsoCodeIsTheBackingValue(): void
    {
        self::assertSame(Jurisdiction::Qld, Jurisdiction::from('AU-QLD'));
        self::assertSame('AU-QLD', Jurisdiction::Qld->isoCode());
    }
}
