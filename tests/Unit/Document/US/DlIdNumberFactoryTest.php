<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\AbstractDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\AlDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\AzDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\CoDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\CtDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\FlDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\HiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\IaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\IdDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\IlDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\InDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\KsDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\KyDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MdDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MeDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\MsDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NdDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NeDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NjDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NmDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\NyDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\OkDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\PaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\RiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\TxDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\VtDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\WaDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\WiDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlId\WyDlIdNumber;
use MSSTC4PHP\ValueObject\Documents\Document\US\DlIdNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
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
 * Expectations follow docs/sources/US.md "Overlap — the factory's test oracle": every worked example, and one input
 * per overlap-table row not already among them. The IL ID-card row (11 digits + a letter) follows the ruling that
 * `IlDlIdNumber` accepts both Illinois shapes.
 */
#[CoversClass(DlIdNumberFactory::class)]
#[UsesClass(AbstractDlIdNumber::class)]
#[UsesClass(AlDlIdNumber::class)]
#[UsesClass(AzDlIdNumber::class)]
#[UsesClass(CoDlIdNumber::class)]
#[UsesClass(CtDlIdNumber::class)]
#[UsesClass(FlDlIdNumber::class)]
#[UsesClass(HiDlIdNumber::class)]
#[UsesClass(IaDlIdNumber::class)]
#[UsesClass(IdDlIdNumber::class)]
#[UsesClass(IlDlIdNumber::class)]
#[UsesClass(InDlIdNumber::class)]
#[UsesClass(KsDlIdNumber::class)]
#[UsesClass(KyDlIdNumber::class)]
#[UsesClass(MaDlIdNumber::class)]
#[UsesClass(MdDlIdNumber::class)]
#[UsesClass(MeDlIdNumber::class)]
#[UsesClass(MiDlIdNumber::class)]
#[UsesClass(MsDlIdNumber::class)]
#[UsesClass(NdDlIdNumber::class)]
#[UsesClass(NeDlIdNumber::class)]
#[UsesClass(NjDlIdNumber::class)]
#[UsesClass(NmDlIdNumber::class)]
#[UsesClass(NyDlIdNumber::class)]
#[UsesClass(OkDlIdNumber::class)]
#[UsesClass(PaDlIdNumber::class)]
#[UsesClass(RiDlIdNumber::class)]
#[UsesClass(TxDlIdNumber::class)]
#[UsesClass(VtDlIdNumber::class)]
#[UsesClass(WaDlIdNumber::class)]
#[UsesClass(WiDlIdNumber::class)]
#[UsesClass(WyDlIdNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(AmbiguousJurisdictionException::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(SubdivisionResolver::class)]
#[UsesClass(Text::class)]
final class DlIdNumberFactoryTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, list<Jurisdiction>}>
     */
    public static function matchingJurisdictions(): iterable
    {
        yield 'example 12345' => ['12345', []];
        yield 'example 123456' => ['123456', [Jurisdiction::KS]];
        yield 'example AB1234' => ['AB1234', [Jurisdiction::KS]];
        yield 'example 1234567' => ['1234567', [Jurisdiction::AL, Jurisdiction::ME, Jurisdiction::RI]];
        yield 'example 12345678' => [
            '12345678',
            [
                Jurisdiction::AL,
                Jurisdiction::PA,
                Jurisdiction::RI,
                Jurisdiction::TX,
                Jurisdiction::VT,
            ],
        ];
        yield 'example 123456789 (Review Focus 1)' => [
            '123456789',
            [
                Jurisdiction::AZ,
                Jurisdiction::CO,
                Jurisdiction::CT,
                Jurisdiction::IA,
                Jurisdiction::ID,
                Jurisdiction::KS,
                Jurisdiction::MS,
                Jurisdiction::NM,
                Jurisdiction::NY,
                Jurisdiction::OK,
                Jurisdiction::WY,
            ],
        ];
        yield 'example 1234567890' => ['1234567890', [Jurisdiction::IN]];
        yield 'example 12345678901' => ['12345678901', []];
        yield 'example 1234567890123' => ['1234567890123', []];
        yield 'example 1234567A' => ['1234567A', [Jurisdiction::VT]];
        yield 'example 2054512V' => ['2054512V', [Jurisdiction::VT]];
        yield 'example 1234567B' => ['1234567B', []];
        yield 'example 123456789A' => ['123456789A', [Jurisdiction::MS]];
        yield 'example A12345678' => [
            'A12345678',
            [
                Jurisdiction::AZ,
                Jurisdiction::IA,
                Jurisdiction::KY,
                Jurisdiction::NE,
            ],
        ];
        yield 'example D12345678' => ['D12345678', [Jurisdiction::AZ, Jurisdiction::IA, Jurisdiction::KY]];
        yield 'example H12345678' => [
            'H12345678',
            [
                Jurisdiction::AZ,
                Jurisdiction::HI,
                Jurisdiction::IA,
                Jurisdiction::KY,
                Jurisdiction::NE,
            ],
        ];
        yield 'example K12345678' => [
            'K12345678',
            [
                Jurisdiction::AZ,
                Jurisdiction::IA,
                Jurisdiction::KS,
                Jurisdiction::KY,
            ],
        ];
        yield 'example S12345678' => [
            'S12345678',
            [
                Jurisdiction::AZ,
                Jurisdiction::IA,
                Jurisdiction::KY,
                Jurisdiction::MA,
            ],
        ];
        yield 'example C21545427' => [
            'C21545427',
            [
                Jurisdiction::AZ,
                Jurisdiction::IA,
                Jurisdiction::KY,
                Jurisdiction::NE,
            ],
        ];
        yield 'example SA1234567' => ['SA1234567', [Jurisdiction::IA, Jurisdiction::MA]];
        yield 'example HABCDEFGH' => ['HABCDEFGH', [Jurisdiction::HI, Jurisdiction::IA]];
        yield 'example A1B2C3D4E' => ['A1B2C3D4E', [Jurisdiction::IA]];
        yield 'example AB123456C' => ['AB123456C', [Jurisdiction::IA, Jurisdiction::ID]];
        yield 'example ABC123456' => ['ABC123456', [Jurisdiction::IA, Jurisdiction::ND]];
        yield 'example A123456789' => ['A123456789', [Jurisdiction::OK]];
        yield 'example Y620-9305-7270' => ['Y620-9305-7270', [Jurisdiction::IL]];
        yield 'example Z123-456-78-901-0' => [
            'Z123-456-78-901-0',
            [
                Jurisdiction::FL,
                Jurisdiction::MD,
                Jurisdiction::MI,
            ],
        ];
        yield 'example MD12345678900' => ['MD12345678900', []];
        yield 'example H300-1987-0162-04' => ['H300-1987-0162-04', [Jurisdiction::WI]];
        yield 'example A12345678901234' => ['A12345678901234', [Jurisdiction::NJ]];
        yield 'example WDL123456789' => ['WDL123456789', [Jurisdiction::WA]];
        yield 'example GROSECM378NA' => ['GROSECM378NA', [Jurisdiction::WA]];
        yield 'example SMITH**12*AB' => ['SMITH**12*AB', [Jurisdiction::WA]];
        yield 'overlap: 6 alphanumerics, letters only' => ['abcdef', [Jurisdiction::KS]];
        yield 'overlap: 7 digits + a letter of the VT set, lower case' => ['1234567x', [Jurisdiction::VT]];
        yield 'overlap: 9 digits + a letter' => ['987654321Z', [Jurisdiction::MS]];
        yield 'overlap: letter + 8 digits, NE letter V' => [
            'V12345678',
            [
                Jurisdiction::AZ,
                Jurisdiction::IA,
                Jurisdiction::KY,
                Jurisdiction::NE,
            ],
        ];
        yield 'overlap: letter + 8 digits, letter outside every set' => [
            'Z12345678',
            [
                Jurisdiction::AZ,
                Jurisdiction::IA,
                Jurisdiction::KY,
            ],
        ];
        yield 'overlap: 9 alphanumerics, H first, not letter + 8 digits' => [
            'H1234567A',
            [
                Jurisdiction::HI,
                Jurisdiction::IA,
            ],
        ];
        yield 'overlap: 9 alphanumerics, no listed layout' => ['1A2B3C4D5', [Jurisdiction::IA]];
        yield 'overlap: letter + 11 digits' => ['A12345678901', [Jurisdiction::IL]];
        yield 'overlap: 11 digits + a letter (IL ID card, ruling 1)' => ['12345678901A', [Jurisdiction::IL]];
        yield 'overlap: letter + 12 digits' => [
            'A123456789012',
            [
                Jurisdiction::FL,
                Jurisdiction::MD,
                Jurisdiction::MI,
            ],
        ];
        yield 'overlap: letter + 13 digits' => ['A1234567890123', [Jurisdiction::WI]];
        yield 'overlap: WDL + 9 letters' => ['WDLABCDEFGHI', [Jurisdiction::WA]];
        yield 'overlap: 1 digit' => ['1', []];
        yield 'overlap: 16 digits' => ['1234567890123456', []];
        yield 'overlap: empty' => ['', []];
    }

    /**
     * @param list<Jurisdiction> $expected
     */
    #[DataProvider('matchingJurisdictions')]
    public function testListsMatchingJurisdictionsInEnumOrder(string $number, array $expected): void
    {
        self::assertSame($expected, DlIdNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * @return iterable<string, array{string, class-string<AbstractDlIdNumber>}>
     */
    public static function singleMatches(): iterable
    {
        yield '123456' => ['123456', KsDlIdNumber::class];
        yield 'AB1234' => ['AB1234', KsDlIdNumber::class];
        yield '1234567890' => ['1234567890', InDlIdNumber::class];
        yield '1234567A' => ['1234567A', VtDlIdNumber::class];
        yield '2054512V' => ['2054512V', VtDlIdNumber::class];
        yield '123456789A' => ['123456789A', MsDlIdNumber::class];
        yield 'A1B2C3D4E' => ['A1B2C3D4E', IaDlIdNumber::class];
        yield 'A123456789' => ['A123456789', OkDlIdNumber::class];
        yield 'Y620-9305-7270' => ['Y620-9305-7270', IlDlIdNumber::class];
        yield 'H300-1987-0162-04' => ['H300-1987-0162-04', WiDlIdNumber::class];
        yield 'A12345678901234' => ['A12345678901234', NjDlIdNumber::class];
        yield 'WDL123456789' => ['WDL123456789', WaDlIdNumber::class];
        yield 'GROSECM378NA' => ['GROSECM378NA', WaDlIdNumber::class];
        yield 'SMITH**12*AB' => ['SMITH**12*AB', WaDlIdNumber::class];
        yield 'overlap: 6 alphanumerics, letters only' => ['abcdef', KsDlIdNumber::class];
        yield 'overlap: 7 digits + a letter of the VT set, lower case' => ['1234567x', VtDlIdNumber::class];
        yield 'overlap: 9 digits + a letter' => ['987654321Z', MsDlIdNumber::class];
        yield 'overlap: 9 alphanumerics, no listed layout' => ['1A2B3C4D5', IaDlIdNumber::class];
        yield 'overlap: letter + 11 digits' => ['A12345678901', IlDlIdNumber::class];
        yield 'overlap: 11 digits + a letter (IL ID card, ruling 1)' => ['12345678901A', IlDlIdNumber::class];
        yield 'overlap: letter + 13 digits' => ['A1234567890123', WiDlIdNumber::class];
        yield 'overlap: WDL + 9 letters' => ['WDLABCDEFGHI', WaDlIdNumber::class];
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     */
    #[DataProvider('singleMatches')]
    public function testReturnsTheOnlyMatchingJurisdiction(string $number, string $class): void
    {
        $matches = DlIdNumberFactory::jurisdictionsMatching($number);
        self::assertCount(1, $matches);
        $licence = DlIdNumberFactory::fromNumberIn($number, $matches[0]);

        self::assertInstanceOf($class, $licence);
        self::assertTrue($class::from($number)->equals($licence));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        yield 'five digits' => ['12345'];
        yield 'eleven digits' => ['12345678901'];
        yield 'sixteen digits' => ['1234567890123456'];
        yield 'thirteen digits, a length FL, MD and MI have' => ['1234567890123'];
        yield 'MD customer ID' => ['MD12345678900'];
        yield 'seven digits + B' => ['1234567B'];
        yield 'empty' => [''];
        yield 'pasted several times' => [\str_repeat('123456789 ', 5)];
    }

    #[DataProvider('numbersValidNowhere')]
    public function testANumberValidNowhereMatchesNoJurisdiction(string $number): void
    {
        self::assertSame([], DlIdNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * @return iterable<string, array{Jurisdiction, class-string<AbstractDlIdNumber>, string}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'US-AL' => [Jurisdiction::AL, AlDlIdNumber::class, '1234567'];
        yield 'US-AZ' => [Jurisdiction::AZ, AzDlIdNumber::class, 'A12345678'];
        yield 'US-CO' => [Jurisdiction::CO, CoDlIdNumber::class, '123456789'];
        yield 'US-CT' => [Jurisdiction::CT, CtDlIdNumber::class, '123456789'];
        yield 'US-FL' => [Jurisdiction::FL, FlDlIdNumber::class, 'Z123456789010'];
        yield 'US-HI' => [Jurisdiction::HI, HiDlIdNumber::class, 'H12345678'];
        yield 'US-IA' => [Jurisdiction::IA, IaDlIdNumber::class, 'A1B2C3D4E'];
        yield 'US-ID' => [Jurisdiction::ID, IdDlIdNumber::class, 'AB123456C'];
        yield 'US-IL' => [Jurisdiction::IL, IlDlIdNumber::class, 'Y62093057270'];
        yield 'US-IN' => [Jurisdiction::IN, InDlIdNumber::class, '1234567890'];
        yield 'US-KS' => [Jurisdiction::KS, KsDlIdNumber::class, 'K12345678'];
        yield 'US-KY' => [Jurisdiction::KY, KyDlIdNumber::class, 'C21545427'];
        yield 'US-MA' => [Jurisdiction::MA, MaDlIdNumber::class, 'S12345678'];
        yield 'US-MD' => [Jurisdiction::MD, MdDlIdNumber::class, 'A999999999999'];
        yield 'US-ME' => [Jurisdiction::ME, MeDlIdNumber::class, '1234567'];
        yield 'US-MI' => [Jurisdiction::MI, MiDlIdNumber::class, 'A123456789012'];
        yield 'US-MS' => [Jurisdiction::MS, MsDlIdNumber::class, '123456789A'];
        yield 'US-ND' => [Jurisdiction::ND, NdDlIdNumber::class, 'ABC123456'];
        yield 'US-NE' => [Jurisdiction::NE, NeDlIdNumber::class, 'A12345678'];
        yield 'US-NJ' => [Jurisdiction::NJ, NjDlIdNumber::class, 'A12345678901234'];
        yield 'US-NM' => [Jurisdiction::NM, NmDlIdNumber::class, '123456789'];
        yield 'US-NY' => [Jurisdiction::NY, NyDlIdNumber::class, '123456789'];
        yield 'US-OK' => [Jurisdiction::OK, OkDlIdNumber::class, 'A123456789'];
        yield 'US-PA' => [Jurisdiction::PA, PaDlIdNumber::class, '12345678'];
        yield 'US-RI' => [Jurisdiction::RI, RiDlIdNumber::class, '12345678'];
        yield 'US-TX' => [Jurisdiction::TX, TxDlIdNumber::class, '12345678'];
        yield 'US-VT' => [Jurisdiction::VT, VtDlIdNumber::class, '3470128A'];
        yield 'US-WA' => [Jurisdiction::WA, WaDlIdNumber::class, 'GROSECM378NA'];
        yield 'US-WI' => [Jurisdiction::WI, WiDlIdNumber::class, 'H3001987016204'];
        yield 'US-WY' => [Jurisdiction::WY, WyDlIdNumber::class, '123456789'];
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     */
    #[DataProvider('concreteClasses')]
    public function testFromNumberInReturnsTheConcreteClass(
        Jurisdiction $jurisdiction,
        string $class,
        string $number,
    ): void {
        $licence = DlIdNumberFactory::fromNumberIn($number, $jurisdiction);

        self::assertInstanceOf($class, $licence);
        self::assertSame($jurisdiction, $licence->jurisdiction());
        self::assertSame($number, $licence->value);
        self::assertContains($jurisdiction, DlIdNumberFactory::jurisdictionsMatching($number));
    }

    /**
     * The 21 jurisdictions of US.md "DL/ID numbers not covered".
     *
     * @return iterable<string, array{Jurisdiction}>
     */
    public static function notCovered(): iterable
    {
        yield 'US-AK' => [Jurisdiction::AK];
        yield 'US-AR' => [Jurisdiction::AR];
        yield 'US-CA' => [Jurisdiction::CA];
        yield 'US-DC' => [Jurisdiction::DC];
        yield 'US-DE' => [Jurisdiction::DE];
        yield 'US-GA' => [Jurisdiction::GA];
        yield 'US-LA' => [Jurisdiction::LA];
        yield 'US-MN' => [Jurisdiction::MN];
        yield 'US-MO' => [Jurisdiction::MO];
        yield 'US-MT' => [Jurisdiction::MT];
        yield 'US-NC' => [Jurisdiction::NC];
        yield 'US-NH' => [Jurisdiction::NH];
        yield 'US-NV' => [Jurisdiction::NV];
        yield 'US-OH' => [Jurisdiction::OH];
        yield 'US-OR' => [Jurisdiction::OR];
        yield 'US-SC' => [Jurisdiction::SC];
        yield 'US-SD' => [Jurisdiction::SD];
        yield 'US-TN' => [Jurisdiction::TN];
        yield 'US-UT' => [Jurisdiction::UT];
        yield 'US-VA' => [Jurisdiction::VA];
        yield 'US-WV' => [Jurisdiction::WV];
    }

    #[DataProvider('notCovered')]
    public function testFromNumberInRefusesAJurisdictionWithoutAPublishedFormat(Jurisdiction $jurisdiction): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DlIdNumberFactory::fromNumberIn($value, $jurisdiction),
            '123456789',
            \sprintf('Invalid %s DL/ID number (format not covered).', $jurisdiction->isoCode()),
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

        self::assertCount(30, $covered);
        self::assertCount(21, $notCovered);
        self::assertSame([], \array_uintersect($covered, $notCovered, $this->compare(...)));

        $all = [...$covered, ...$notCovered];
        \usort($all, $this->compare(...));
        self::assertSame(Jurisdiction::cases(), $all);
    }

    public function testFromNumberInRefusesANumberOfAnotherJurisdiction(): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DlIdNumberFactory::fromNumberIn($value, Jurisdiction::NY),
            '12345678',
            'Invalid US-NY DL/ID number (wrong length).',
        );
    }

    public function testTryFromNumberInBuildsWhatFromNumberInBuilds(): void
    {
        $built = DlIdNumberFactory::tryFromNumberIn('123456789', Jurisdiction::NY);

        self::assertNotNull($built);
        self::assertEquals(DlIdNumberFactory::fromNumberIn('123456789', Jurisdiction::NY), $built);
    }

    public function testTryFromNumberInIsNullForANumberTheJurisdictionRefuses(): void
    {
        self::assertNull(DlIdNumberFactory::tryFromNumberIn('12345678', Jurisdiction::NY));
    }

    public function testTryFromNumberInIsNullForAJurisdictionWithoutAPublishedFormat(): void
    {
        self::assertNull(DlIdNumberFactory::tryFromNumberIn('123456789', Jurisdiction::CA));
    }

    private function compare(Jurisdiction $left, Jurisdiction $right): int
    {
        return \array_search($left, Jurisdiction::cases(), true) <=> \array_search($right, Jurisdiction::cases(), true);
    }
}
