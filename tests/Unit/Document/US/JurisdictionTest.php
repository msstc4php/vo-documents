<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Jurisdiction::class)]
final class JurisdictionTest extends TestCase
{
    public function testCasesAreTheFiftyStatesAndDcInIsoOrder(): void
    {
        $codes = \array_map(
            static fn (Jurisdiction $jurisdiction): string => $jurisdiction->isoCode(),
            Jurisdiction::cases(),
        );

        self::assertSame(
            [
                'US-AK', 'US-AL', 'US-AR', 'US-AZ', 'US-CA', 'US-CO', 'US-CT', 'US-DC', 'US-DE', 'US-FL', 'US-GA',
                'US-HI', 'US-IA', 'US-ID', 'US-IL', 'US-IN', 'US-KS', 'US-KY', 'US-LA', 'US-MA', 'US-MD', 'US-ME',
                'US-MI', 'US-MN', 'US-MO', 'US-MS', 'US-MT', 'US-NC', 'US-ND', 'US-NE', 'US-NH', 'US-NJ', 'US-NM',
                'US-NV', 'US-NY', 'US-OH', 'US-OK', 'US-OR', 'US-PA', 'US-RI', 'US-SC', 'US-SD', 'US-TN', 'US-TX',
                'US-UT', 'US-VA', 'US-VT', 'US-WA', 'US-WI', 'US-WV', 'US-WY',
            ],
            $codes,
        );
    }

    public function testIsoCodeIsTheBackingValue(): void
    {
        self::assertSame(Jurisdiction::NY, Jurisdiction::from('US-NY'));
        self::assertSame('US-DC', Jurisdiction::DC->isoCode());
    }
}
