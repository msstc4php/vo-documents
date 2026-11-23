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
        yield 'example 123456789012' => ['123456789012', [Jurisdiction::Mex]];
        yield 'example 1234 5678 9012' => ['1234 5678 9012', [Jurisdiction::Mex]];
        yield 'example 12345678901' => ['12345678901', []];
        yield 'example 1234567890123' => ['1234567890123', []];
        yield 'example 12345678901A' => ['12345678901A', []];
        yield 'overlap: 12 digits' => ['000000000001', [Jurisdiction::Mex]];
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
        $licence = DriverLicenceNumberFactory::fromNumber($number);

        self::assertInstanceOf(MexDriverLicenceNumber::class, $licence);
        self::assertSame($value, (string) $licence);
        self::assertTrue(MexDriverLicenceNumber::from($number)->equals($licence));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        yield 'empty' => ['', "Invalid Mexican driver's licence number (wrong length)."];
        yield 'eleven digits' => ['12345678901', "Invalid Mexican driver's licence number (wrong length)."];
        yield 'thirteen digits' => ['1234567890123', "Invalid Mexican driver's licence number (wrong length)."];
        yield 'twelve characters with a letter' => [
            '12345678901A',
            "Invalid Mexican driver's licence number (fits no jurisdiction).",
        ];
        yield 'pasted several times' => [
            \str_repeat('123456789012 ', 5),
            "Invalid Mexican driver's licence number (wrong length).",
        ];
    }

    #[DataProvider('numbersValidNowhere')]
    public function testRefusesANumberValidNowhereWithoutEchoingIt(string $number, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumber($value),
            $number,
            $message,
        );
    }

    /**
     * @return iterable<string, array{Jurisdiction, class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'MX-MEX' => [Jurisdiction::Mex, MexDriverLicenceNumber::class, '1234-5678-9012', '123456789012'];
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
        yield 'MX-AGU' => [Jurisdiction::Agu];
        yield 'MX-BCN' => [Jurisdiction::Bcn];
        yield 'MX-BCS' => [Jurisdiction::Bcs];
        yield 'MX-CAM' => [Jurisdiction::Cam];
        yield 'MX-CHH' => [Jurisdiction::Chh];
        yield 'MX-CHP' => [Jurisdiction::Chp];
        yield 'MX-CMX' => [Jurisdiction::Cmx];
        yield 'MX-COA' => [Jurisdiction::Coa];
        yield 'MX-COL' => [Jurisdiction::Col];
        yield 'MX-DUR' => [Jurisdiction::Dur];
        yield 'MX-GRO' => [Jurisdiction::Gro];
        yield 'MX-GUA' => [Jurisdiction::Gua];
        yield 'MX-HID' => [Jurisdiction::Hid];
        yield 'MX-JAL' => [Jurisdiction::Jal];
        yield 'MX-MIC' => [Jurisdiction::Mic];
        yield 'MX-MOR' => [Jurisdiction::Mor];
        yield 'MX-NAY' => [Jurisdiction::Nay];
        yield 'MX-NLE' => [Jurisdiction::Nle];
        yield 'MX-OAX' => [Jurisdiction::Oax];
        yield 'MX-PUE' => [Jurisdiction::Pue];
        yield 'MX-QUE' => [Jurisdiction::Que];
        yield 'MX-ROO' => [Jurisdiction::Roo];
        yield 'MX-SIN' => [Jurisdiction::Sin];
        yield 'MX-SLP' => [Jurisdiction::Slp];
        yield 'MX-SON' => [Jurisdiction::Son];
        yield 'MX-TAB' => [Jurisdiction::Tab];
        yield 'MX-TAM' => [Jurisdiction::Tam];
        yield 'MX-TLA' => [Jurisdiction::Tla];
        yield 'MX-VER' => [Jurisdiction::Ver];
        yield 'MX-YUC' => [Jurisdiction::Yuc];
        yield 'MX-ZAC' => [Jurisdiction::Zac];
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
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumberIn($value, Jurisdiction::Mex),
            '12345678',
            "Invalid MX-MEX driver's licence number (wrong length).",
        );
    }

    private function compare(Jurisdiction $left, Jurisdiction $right): int
    {
        return \array_search($left, Jurisdiction::cases(), true) <=> \array_search($right, Jurisdiction::cases(), true);
    }
}
