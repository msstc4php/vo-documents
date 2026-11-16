<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Document\MX\Jurisdiction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Jurisdiction::class)]
final class JurisdictionTest extends TestCase
{
    public function testCasesAreTheThirtyTwoFederalEntitiesInIsoOrder(): void
    {
        $codes = \array_map(
            static fn (Jurisdiction $jurisdiction): string => $jurisdiction->isoCode(),
            Jurisdiction::cases(),
        );

        self::assertSame(
            [
                'MX-AGU', 'MX-BCN', 'MX-BCS', 'MX-CAM', 'MX-CHH', 'MX-CHP', 'MX-CMX', 'MX-COA', 'MX-COL', 'MX-DUR',
                'MX-GRO', 'MX-GUA', 'MX-HID', 'MX-JAL', 'MX-MEX', 'MX-MIC', 'MX-MOR', 'MX-NAY', 'MX-NLE', 'MX-OAX',
                'MX-PUE', 'MX-QUE', 'MX-ROO', 'MX-SIN', 'MX-SLP', 'MX-SON', 'MX-TAB', 'MX-TAM', 'MX-TLA', 'MX-VER',
                'MX-YUC', 'MX-ZAC',
            ],
            $codes,
        );
    }

    public function testIsoCodeIsTheBackingValue(): void
    {
        self::assertSame(Jurisdiction::Cmx, Jurisdiction::from('MX-CMX'));
        self::assertSame('MX-MEX', Jurisdiction::Mex->isoCode());
    }
}
