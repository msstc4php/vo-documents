<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\AbDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\BcDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\NsDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\OnDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\QcDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\SkDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
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
 * Expectations follow docs/sources/CA.md "Driver's licence numbers — overlap (the factory's test oracle)": every worked
 * example and one input per overlap-table row not already among them. BC and SK share every shape, so their numbers
 * reach a single class only through `fromNumberIn()`.
 */
#[CoversClass(DriverLicenceNumberFactory::class)]
#[UsesClass(AbstractDriverLicenceNumber::class)]
#[UsesClass(AbDriverLicenceNumber::class)]
#[UsesClass(BcDriverLicenceNumber::class)]
#[UsesClass(NsDriverLicenceNumber::class)]
#[UsesClass(OnDriverLicenceNumber::class)]
#[UsesClass(QcDriverLicenceNumber::class)]
#[UsesClass(SkDriverLicenceNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(AmbiguousJurisdictionException::class)]
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
        $bcSk = [Jurisdiction::Bc, Jurisdiction::Sk];

        yield 'example (empty)' => ['', []];
        yield 'example 1' => ['1', $bcSk];
        yield 'example 347631' => ['347631', $bcSk];
        yield 'example 890562' => ['890562', $bcSk];
        yield 'example 1234567' => ['1234567', $bcSk];
        yield 'example 0567450' => ['0567450', $bcSk];
        yield 'example 12345678' => ['12345678', $bcSk];
        yield 'example 31098794' => ['31098794', $bcSk];
        yield 'example 35890562' => ['35890562', $bcSk];
        yield 'example 123456789' => ['123456789', [Jurisdiction::Ab]];
        yield 'example 123456-789' => ['123456-789', [Jurisdiction::Ab]];
        yield 'example 1234567890' => ['1234567890', []];
        yield 'example FINCH220867001' => ['FINCH220867001', [Jurisdiction::Ns]];
        yield 'example FINCH--220867001' => ['FINCH--220867001', [Jurisdiction::Ns]];
        yield 'example FOY  220867001' => ['FOY  220867001', [Jurisdiction::Ns]];
        yield 'example finch 220867 001' => ['finch 220867 001', [Jurisdiction::Ns]];
        yield 'example FINCH320867001 (day 32)' => ['FINCH320867001', []];
        yield 'example FINCH221367001 (month 13)' => ['FINCH221367001', []];
        yield 'example FINCHE220867001' => ['FINCHE220867001', [Jurisdiction::On]];
        yield 'example L1531-171274-08' => ['L1531-171274-08', [Jurisdiction::Qc]];
        yield 'example l153117127408' => ['l153117127408', [Jurisdiction::Qc]];
        yield 'example LL53117127408' => ['LL53117127408', []];
        yield 'example LL531171274080 (2 letters + 12 digits)' => ['LL531171274080', []];
        yield 'example X0915-26435-95718' => ['X0915-26435-95718', [Jurisdiction::On]];
        yield 'example X09152643595718' => ['X09152643595718', [Jurisdiction::On]];
        yield 'example ABCDEFGHIJKLMNO' => ['ABCDEFGHIJKLMNO', [Jurisdiction::On]];
        yield 'example X0915264359571' => ['X0915264359571', []];
        yield 'example X091526435957189' => ['X091526435957189', []];
        yield 'overlap: one letter + valid DDMM + 5 digits' => ['A010100001', [Jurisdiction::Ns]];
        yield 'overlap: 15 digits' => ['123456789012345', [Jurisdiction::On]];
        yield 'overlap: 16 digits' => ['1234567890123456', []];
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
     * @return iterable<string, array{string}>
     */
    public static function bcAndSkNumbers(): iterable
    {
        yield 'eight digits' => ['12345678'];
        yield 'ICBC integer example' => ['347631'];
    }

    /**
     * @return iterable<string, array{string, class-string<AbstractDriverLicenceNumber>, string}>
     */
    public static function singleMatches(): iterable
    {
        yield '123456789' => ['123456789', AbDriverLicenceNumber::class, '123456789'];
        yield '123456-789' => ['123456-789', AbDriverLicenceNumber::class, '123456789'];
        yield 'FINCH--220867001' => ['FINCH--220867001', NsDriverLicenceNumber::class, 'FINCH220867001'];
        yield 'FOY  220867001' => ['FOY  220867001', NsDriverLicenceNumber::class, 'FOY220867001'];
        yield 'finch 220867 001' => ['finch 220867 001', NsDriverLicenceNumber::class, 'FINCH220867001'];
        yield 'FINCHE220867001' => ['FINCHE220867001', OnDriverLicenceNumber::class, 'FINCHE220867001'];
        yield 'L1531-171274-08' => ['L1531-171274-08', QcDriverLicenceNumber::class, 'L153117127408'];
        yield 'l153117127408' => ['l153117127408', QcDriverLicenceNumber::class, 'L153117127408'];
        yield 'X0915-26435-95718' => ['X0915-26435-95718', OnDriverLicenceNumber::class, 'X09152643595718'];
        yield 'ABCDEFGHIJKLMNO' => ['ABCDEFGHIJKLMNO', OnDriverLicenceNumber::class, 'ABCDEFGHIJKLMNO'];
        yield 'overlap: one letter + valid DDMM + 5 digits' => [
            'A010100001',
            NsDriverLicenceNumber::class,
            'A010100001',
        ];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('singleMatches')]
    public function testReturnsTheOnlyMatchingJurisdiction(string $number, string $class, string $value): void
    {
        $matches = DriverLicenceNumberFactory::jurisdictionsMatching($number);
        self::assertCount(1, $matches);
        $licence = DriverLicenceNumberFactory::fromNumberIn($number, $matches[0]);

        self::assertInstanceOf($class, $licence);
        self::assertSame($value, (string) $licence);
        self::assertTrue($class::from($number)->equals($licence));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        yield 'empty' => [''];
        yield 'ten digits, an NS length' => ['1234567890'];
        yield 'NS layout, day 32' => ['FINCH320867001'];
        yield 'NS layout, month 13' => ['FINCH221367001'];
        yield 'two letters + 12 digits' => ['LL53117127408'];
        yield 'fourteen alphanumerics' => ['X0915264359571'];
        yield 'sixteen alphanumerics' => ['X091526435957189'];
        yield 'pasted several times' => [\str_repeat('123456789 ', 5)];
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
        yield 'CA-AB' => [Jurisdiction::Ab, AbDriverLicenceNumber::class, '123456-789', '123456789'];
        yield 'CA-BC' => [Jurisdiction::Bc, BcDriverLicenceNumber::class, '347631', '00347631'];
        yield 'CA-NS' => [Jurisdiction::Ns, NsDriverLicenceNumber::class, 'FINCH--220867001', 'FINCH220867001'];
        yield 'CA-ON' => [Jurisdiction::On, OnDriverLicenceNumber::class, 'X0915-26435-95718', 'X09152643595718'];
        yield 'CA-QC' => [Jurisdiction::Qc, QcDriverLicenceNumber::class, 'L1531-171274-08', 'L153117127408'];
        yield 'CA-SK' => [Jurisdiction::Sk, SkDriverLicenceNumber::class, '890562', '00890562'];
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
     * The 7 jurisdictions of CA.md "Provincial documents not covered".
     *
     * @return iterable<string, array{Jurisdiction}>
     */
    public static function notCovered(): iterable
    {
        yield 'CA-MB' => [Jurisdiction::Mb];
        yield 'CA-NB' => [Jurisdiction::Nb];
        yield 'CA-NL' => [Jurisdiction::Nl];
        yield 'CA-NT' => [Jurisdiction::Nt];
        yield 'CA-NU' => [Jurisdiction::Nu];
        yield 'CA-PE' => [Jurisdiction::Pe];
        yield 'CA-YT' => [Jurisdiction::Yt];
    }

    #[DataProvider('notCovered')]
    public function testFromNumberInRefusesAJurisdictionWithoutAPublishedFormat(Jurisdiction $jurisdiction): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumberIn($value, $jurisdiction),
            '123456789',
            \sprintf("Invalid %s driver's licence number (format not covered).", $jurisdiction->isoCode()),
        );
    }

    public function testEveryJurisdictionIsEitherCoveredOrListedAsNotCovered(): void
    {
        $covered = [];
        foreach (self::concreteClasses() as [$jurisdiction]) {
            $covered[] = $jurisdiction;
        }

        $notCovered = [];
        foreach (self::notCovered() as [$jurisdiction]) {
            $notCovered[] = $jurisdiction;
        }

        self::assertCount(6, $covered);
        self::assertCount(7, $notCovered);
        self::assertSame([], \array_uintersect($covered, $notCovered, $this->compare(...)));

        $all = [...$covered, ...$notCovered];
        \usort($all, $this->compare(...));
        self::assertSame(Jurisdiction::cases(), $all);
    }

    public function testFromNumberInRefusesANumberOfAnotherJurisdiction(): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumberIn($value, Jurisdiction::Ab),
            '12345678',
            "Invalid CA-AB driver's licence number (wrong length).",
        );
    }

    private function compare(Jurisdiction $left, Jurisdiction $right): int
    {
        return \array_search($left, Jurisdiction::cases(), true) <=> \array_search($right, Jurisdiction::cases(), true);
    }
}
