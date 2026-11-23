<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document;

use MSSTC4PHP\ValueObject\Documents\Document\AU;
use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCardNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCardNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\MX;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlIdNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Owner decision O2 of the 1.0 release: `fromNumber()` exists only where every jurisdiction is covered, so a single
 * match always identifies the issuer.
 */
#[CoversNothing]
final class JurisdictionFactoriesTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function partlyCovered(): iterable
    {
        yield 'US DL/ID' => [DlIdNumberFactory::class];
        yield 'CA driver licence' => [DriverLicenceNumberFactory::class];
        yield 'MX driver licence' => [MX\DriverLicenceNumberFactory::class];
        yield 'BR identity card' => [IdentityCardNumberFactory::class];
    }

    /**
     * @param class-string $factory
     */
    #[DataProvider('partlyCovered')]
    public function testAPartlyCoveredCountryHasNoFromNumber(string $factory): void
    {
        self::assertFalse(\method_exists($factory, 'fromNumber'));
        self::assertTrue(\method_exists($factory, 'jurisdictionsMatching'));
    }

    public function testAustraliaCoversEveryJurisdiction(): void
    {
        foreach (Jurisdiction::cases() as $jurisdiction) {
            self::assertNotSame([], \array_filter(
                ['1234567', '123456', '12345678', '123456789'],
                static fn (string $number): bool => \in_array($jurisdiction, AU\DriverLicenceNumberFactory::jurisdictionsMatching($number), true),
            ));
        }
    }

    public function testCanadaCoversEveryHealthCardJurisdiction(): void
    {
        foreach (CA\Jurisdiction::cases() as $jurisdiction) {
            try {
                HealthCardNumberFactory::fromNumberIn('', $jurisdiction);
                self::fail('An empty number is never valid.');
            } catch (InvalidDocumentNumberException $exception) {
                self::assertStringNotContainsString('format not covered', $exception->getMessage());
            }
        }
    }
}
