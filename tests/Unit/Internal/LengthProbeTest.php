<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\SaDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\LengthProbe;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[CoversClass(LengthProbe::class)]
#[UsesNamespace('MSSTC4PHP\ValueObject\Documents')]
final class LengthProbeTest extends TestCase
{
    public function testCallsTheProtectedHook(): void
    {
        self::assertTrue(LengthProbe::fits(SaDriverLicenceNumber::class, '12-34 56'));
        self::assertFalse(LengthProbe::fits(SaDriverLicenceNumber::class, '1234567'));
    }

    public function testTheHookIsNoPublicApi(): void
    {
        self::assertTrue(new ReflectionMethod(SaDriverLicenceNumber::class, 'fitsLength')->isProtected());
    }
}
