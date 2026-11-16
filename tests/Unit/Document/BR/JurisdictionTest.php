<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Jurisdiction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Jurisdiction::class)]
final class JurisdictionTest extends TestCase
{
    public function testCasesAreTheTwentySevenFederativeUnitsInIsoOrder(): void
    {
        $codes = \array_map(
            static fn (Jurisdiction $jurisdiction): string => $jurisdiction->isoCode(),
            Jurisdiction::cases(),
        );

        self::assertSame(
            [
                'BR-AC', 'BR-AL', 'BR-AM', 'BR-AP', 'BR-BA', 'BR-CE', 'BR-DF', 'BR-ES', 'BR-GO', 'BR-MA', 'BR-MG',
                'BR-MS', 'BR-MT', 'BR-PA', 'BR-PB', 'BR-PE', 'BR-PI', 'BR-PR', 'BR-RJ', 'BR-RN', 'BR-RO', 'BR-RR',
                'BR-RS', 'BR-SC', 'BR-SE', 'BR-SP', 'BR-TO',
            ],
            $codes,
        );
    }

    public function testIsoCodeIsTheBackingValue(): void
    {
        self::assertSame(Jurisdiction::Df, Jurisdiction::from('BR-DF'));
        self::assertSame('BR-SP', Jurisdiction::Sp->isoCode());
    }
}
