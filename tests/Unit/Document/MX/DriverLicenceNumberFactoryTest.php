<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence\MexDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\MX\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\SubdivisionResolver;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/MX.md "State driving licences — overlap (the factory's test oracle)": every
 * worked example and one input per overlap-table row. Only MX-MEX is covered, so no number is ambiguous.
 */
#[CoversClass(DriverLicenceNumberFactory::class)]
#[UsesClass(AbstractDriverLicenceNumber::class)]
#[UsesClass(MexDriverLicenceNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
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
        yield 'example (empty)' => ['', []];
        yield 'example 123456789012' => ['123456789012', [Jurisdiction::MEX]];
        yield 'example 1234 5678 9012' => ['1234 5678 9012', [Jurisdiction::MEX]];
        yield 'example 12345678901' => ['12345678901', []];
        yield 'example 1234567890123' => ['1234567890123', []];
        yield 'example 12345678901A' => ['12345678901A', []];
        yield 'overlap: 12 digits' => ['000000000001', [Jurisdiction::MEX]];
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
     * @return iterable<string, array{string, string}>
     */
    public static function singleMatches(): iterable
    {
        yield '123456789012' => ['123456789012', '123456789012'];
        yield '1234 5678 9012' => ['1234 5678 9012', '123456789012'];
    }

    #[DataProvider('singleMatches')]
    public function testReturnsTheOnlyMatchingJurisdiction(string $number, string $value): void
    {
        $matches = DriverLicenceNumberFactory::jurisdictionsMatching($number);
        self::assertCount(1, $matches);
        $licence = DriverLicenceNumberFactory::fromNumberIn($number, $matches[0]);

        self::assertInstanceOf(MexDriverLicenceNumber::class, $licence);
        self::assertSame($value, (string) $licence);
        self::assertTrue(MexDriverLicenceNumber::from($number)->equals($licence));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        yield 'empty' => [''];
        yield 'eleven digits' => ['12345678901'];
        yield 'thirteen digits' => ['1234567890123'];
        yield 'twelve characters with a letter' => ['12345678901A'];
        yield 'pasted several times' => [\str_repeat('123456789012 ', 5)];
    }

    #[DataProvider('numbersValidNowhere')]
    public function testANumberValidNowhereMatchesNoJurisdiction(string $number): void
    {
        self::assertSame([], DriverLicenceNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * @return iterable<string, array{Jurisdiction, class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'MX-MEX' => [Jurisdiction::MEX, MexDriverLicenceNumber::class, '1234-5678-9012', '123456789012'];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('concreteClasses')]
    public function testFromNumberInReturnsTheConcreteClass(
        Jurisdiction $jurisdiction,
        string $class,
        string $number,
        string $value,
    ): void {
        $licence = DriverLicenceNumberFactory::fromNumberIn($number, $jurisdiction);

        self::assertInstanceOf($class, $licence);
        self::assertSame($jurisdiction, $licence->jurisdiction());
        self::assertSame($value, (string) $licence);
        self::assertContains($jurisdiction, DriverLicenceNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * The 31 entities of MX.md "State driving licences — per entity" with a "not covered" verdict.
     *
     * @return iterable<string, array{Jurisdiction}>
     */
    public static function notCovered(): iterable
    {
        yield 'MX-AGU' => [Jurisdiction::AGU];
        yield 'MX-BCN' => [Jurisdiction::BCN];
        yield 'MX-BCS' => [Jurisdiction::BCS];
        yield 'MX-CAM' => [Jurisdiction::CAM];
        yield 'MX-CHH' => [Jurisdiction::CHH];
        yield 'MX-CHP' => [Jurisdiction::CHP];
        yield 'MX-CMX' => [Jurisdiction::CMX];
        yield 'MX-COA' => [Jurisdiction::COA];
        yield 'MX-COL' => [Jurisdiction::COL];
        yield 'MX-DUR' => [Jurisdiction::DUR];
        yield 'MX-GRO' => [Jurisdiction::GRO];
        yield 'MX-GUA' => [Jurisdiction::GUA];
        yield 'MX-HID' => [Jurisdiction::HID];
        yield 'MX-JAL' => [Jurisdiction::JAL];
        yield 'MX-MIC' => [Jurisdiction::MIC];
        yield 'MX-MOR' => [Jurisdiction::MOR];
        yield 'MX-NAY' => [Jurisdiction::NAY];
        yield 'MX-NLE' => [Jurisdiction::NLE];
        yield 'MX-OAX' => [Jurisdiction::OAX];
        yield 'MX-PUE' => [Jurisdiction::PUE];
        yield 'MX-QUE' => [Jurisdiction::QUE];
        yield 'MX-ROO' => [Jurisdiction::ROO];
        yield 'MX-SIN' => [Jurisdiction::SIN];
        yield 'MX-SLP' => [Jurisdiction::SLP];
        yield 'MX-SON' => [Jurisdiction::SON];
        yield 'MX-TAB' => [Jurisdiction::TAB];
        yield 'MX-TAM' => [Jurisdiction::TAM];
        yield 'MX-TLA' => [Jurisdiction::TLA];
        yield 'MX-VER' => [Jurisdiction::VER];
        yield 'MX-YUC' => [Jurisdiction::YUC];
        yield 'MX-ZAC' => [Jurisdiction::ZAC];
    }

    #[DataProvider('notCovered')]
    public function testFromNumberInRefusesAnEntityWithoutAPublishedFormat(Jurisdiction $jurisdiction): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumberIn($value, $jurisdiction),
            '123456789012',
            \sprintf("Invalid %s driver's licence number (format not covered).", $jurisdiction->isoCode()),
        );
    }

    public function testEveryEntityIsEitherCoveredOrListedAsNotCovered(): void
    {
        $covered = [];
        foreach (self::concreteClasses() as [$jurisdiction]) {
            $covered[] = $jurisdiction;
        }

        $notCovered = [];
        foreach (self::notCovered() as [$jurisdiction]) {
            $notCovered[] = $jurisdiction;
        }

        self::assertCount(1, $covered);
        self::assertCount(31, $notCovered);
        self::assertSame([], \array_uintersect($covered, $notCovered, $this->compare(...)));

        $all = [...$covered, ...$notCovered];
        \usort($all, $this->compare(...));
        self::assertSame(Jurisdiction::cases(), $all);
    }

    public function testFromNumberInRefusesANumberOfTheWrongShape(): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumberIn($value, Jurisdiction::MEX),
            '12345678',
            "Invalid MX-MEX driver's licence number (wrong length).",
        );
    }

    private function compare(Jurisdiction $left, Jurisdiction $right): int
    {
        return \array_search($left, Jurisdiction::cases(), true) <=> \array_search($right, Jurisdiction::cases(), true);
    }
}
