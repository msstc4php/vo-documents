<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\AU;

use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\ActDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NswDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NtDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\QldDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\SaDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\TasDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\VicDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\WaDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/AU.md "The jurisdiction factory": the published formats overlap so that no number
 * fits exactly one jurisdiction.
 */
#[CoversClass(DriverLicenceNumberFactory::class)]
#[UsesClass(AbstractDriverLicenceNumber::class)]
#[UsesClass(ActDriverLicenceNumber::class)]
#[UsesClass(NswDriverLicenceNumber::class)]
#[UsesClass(NtDriverLicenceNumber::class)]
#[UsesClass(QldDriverLicenceNumber::class)]
#[UsesClass(SaDriverLicenceNumber::class)]
#[UsesClass(TasDriverLicenceNumber::class)]
#[UsesClass(VicDriverLicenceNumber::class)]
#[UsesClass(WaDriverLicenceNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(SubdivisionResolver::class)]
#[UsesClass(Text::class)]
final class DriverLicenceNumberFactoryTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, list<Jurisdiction>}>
     */
    public static function matchingJurisdictions(): iterable
    {
        yield 'one digit' => ['7', [Jurisdiction::ACT, Jurisdiction::NT, Jurisdiction::VIC]];
        yield 'six digits' => [
            '000000',
            [
                Jurisdiction::ACT,
                Jurisdiction::NSW,
                Jurisdiction::NT,
                Jurisdiction::SA,
                Jurisdiction::TAS,
                Jurisdiction::VIC,
            ],
        ];
        yield 'seven digits (WA sample)' => [
            '6437772',
            [
                Jurisdiction::ACT,
                Jurisdiction::NSW,
                Jurisdiction::NT,
                Jurisdiction::TAS,
                Jurisdiction::VIC,
                Jurisdiction::WA,
            ],
        ];
        yield 'eight digits (NSW sample)' => [
            '01234567',
            [
                Jurisdiction::ACT,
                Jurisdiction::NSW,
                Jurisdiction::NT,
                Jurisdiction::QLD,
                Jurisdiction::TAS,
                Jurisdiction::VIC,
            ],
        ];
        yield 'nine digits (Qld sample)' => [
            '123 456 789',
            [Jurisdiction::ACT, Jurisdiction::NT, Jurisdiction::QLD, Jurisdiction::VIC],
        ];
        yield 'ten digits (ACT sample)' => ['1234567890', [Jurisdiction::ACT, Jurisdiction::NT, Jurisdiction::VIC]];
        yield 'six characters with a letter' => ['s12345', [Jurisdiction::NSW, Jurisdiction::SA, Jurisdiction::TAS]];
        yield 'eight characters with a letter' => ['AB123456', [Jurisdiction::NSW, Jurisdiction::TAS]];
        yield 'eleven digits' => ['12345678901', []];
        yield 'nine characters with a letter' => ['A12345678', []];
        yield 'empty' => ['', []];
    }

    /**
     * @param list<Jurisdiction> $expected
     */
    #[DataProvider('matchingJurisdictions')]
    public function testListsMatchingJurisdictionsInEnumOrder(string $number, array $expected): void
    {
        self::assertSame($expected, DriverLicenceNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * Why there is no `fromNumber()`: no number of the sample set fits exactly one jurisdiction.
     */
    public function testNoNumberFitsExactlyOneJurisdiction(): void
    {
        foreach (self::matchingJurisdictions() as $label => [$number]) {
            self::assertNotCount(1, DriverLicenceNumberFactory::jurisdictionsMatching($number), $label);
        }
    }

    /**
     * @return iterable<string, array{Jurisdiction, class-string<AbstractDriverLicenceNumber>}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'ACT' => [Jurisdiction::ACT, ActDriverLicenceNumber::class];
        yield 'NSW' => [Jurisdiction::NSW, NswDriverLicenceNumber::class];
        yield 'NT' => [Jurisdiction::NT, NtDriverLicenceNumber::class];
        yield 'Qld' => [Jurisdiction::QLD, QldDriverLicenceNumber::class];
        yield 'SA' => [Jurisdiction::SA, SaDriverLicenceNumber::class];
        yield 'Tas' => [Jurisdiction::TAS, TasDriverLicenceNumber::class];
        yield 'Vic' => [Jurisdiction::VIC, VicDriverLicenceNumber::class];
        yield 'WA' => [Jurisdiction::WA, WaDriverLicenceNumber::class];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('concreteClasses')]
    public function testFromNumberInReturnsTheConcreteClass(Jurisdiction $jurisdiction, string $class): void
    {
        $number = match ($jurisdiction) {
            Jurisdiction::QLD => '12345678',
            Jurisdiction::WA => '6437772',
            default => '123456',
        };

        $licence = DriverLicenceNumberFactory::fromNumberIn($number, $jurisdiction);

        self::assertInstanceOf($class, $licence);
        self::assertSame($jurisdiction, $licence->jurisdiction());
        self::assertSame($number, $licence->value);
    }

    public function testEveryJurisdictionHasALicenceClass(): void
    {
        $covered = [];
        foreach (self::concreteClasses() as [$jurisdiction]) {
            $covered[] = $jurisdiction;
        }

        self::assertSame(Jurisdiction::cases(), $covered);
    }

    public function testFromNumberInRefusesANumberOfAnotherJurisdiction(): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumberIn($value, Jurisdiction::WA),
            '01234567',
            'Invalid AU-WA driver licence number (wrong length).',
        );
    }
}
