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
        yield 'example 123456' => ['123456', [Jurisdiction::Ks]];
        yield 'example AB1234' => ['AB1234', [Jurisdiction::Ks]];
        yield 'example 1234567' => ['1234567', [Jurisdiction::Al, Jurisdiction::Me, Jurisdiction::Ri]];
        yield 'example 12345678' => [
            '12345678',
            [
                Jurisdiction::Al,
                Jurisdiction::Pa,
                Jurisdiction::Ri,
                Jurisdiction::Tx,
                Jurisdiction::Vt,
            ],
        ];
        yield 'example 123456789 (Review Focus 1)' => [
            '123456789',
            [
                Jurisdiction::Az,
                Jurisdiction::Co,
                Jurisdiction::Ct,
                Jurisdiction::Ia,
                Jurisdiction::Id,
                Jurisdiction::Ks,
                Jurisdiction::Ms,
                Jurisdiction::Nm,
                Jurisdiction::Ny,
                Jurisdiction::Ok,
                Jurisdiction::Wy,
            ],
        ];
        yield 'example 1234567890' => ['1234567890', [Jurisdiction::In]];
        yield 'example 12345678901' => ['12345678901', []];
        yield 'example 1234567890123' => ['1234567890123', []];
        yield 'example 1234567A' => ['1234567A', [Jurisdiction::Vt]];
        yield 'example 2054512V' => ['2054512V', [Jurisdiction::Vt]];
        yield 'example 1234567B' => ['1234567B', []];
        yield 'example 123456789A' => ['123456789A', [Jurisdiction::Ms]];
        yield 'example A12345678' => [
            'A12345678',
            [
                Jurisdiction::Az,
                Jurisdiction::Ia,
                Jurisdiction::Ky,
                Jurisdiction::Ne,
            ],
        ];
        yield 'example D12345678' => ['D12345678', [Jurisdiction::Az, Jurisdiction::Ia, Jurisdiction::Ky]];
        yield 'example H12345678' => [
            'H12345678',
            [
                Jurisdiction::Az,
                Jurisdiction::Hi,
                Jurisdiction::Ia,
                Jurisdiction::Ky,
                Jurisdiction::Ne,
            ],
        ];
        yield 'example K12345678' => [
            'K12345678',
            [
                Jurisdiction::Az,
                Jurisdiction::Ia,
                Jurisdiction::Ks,
                Jurisdiction::Ky,
            ],
        ];
        yield 'example S12345678' => [
            'S12345678',
            [
                Jurisdiction::Az,
                Jurisdiction::Ia,
                Jurisdiction::Ky,
                Jurisdiction::Ma,
            ],
        ];
        yield 'example C21545427' => [
            'C21545427',
            [
                Jurisdiction::Az,
                Jurisdiction::Ia,
                Jurisdiction::Ky,
                Jurisdiction::Ne,
            ],
        ];
        yield 'example SA1234567' => ['SA1234567', [Jurisdiction::Ia, Jurisdiction::Ma]];
        yield 'example HABCDEFGH' => ['HABCDEFGH', [Jurisdiction::Hi, Jurisdiction::Ia]];
        yield 'example A1B2C3D4E' => ['A1B2C3D4E', [Jurisdiction::Ia]];
        yield 'example AB123456C' => ['AB123456C', [Jurisdiction::Ia, Jurisdiction::Id]];
        yield 'example ABC123456' => ['ABC123456', [Jurisdiction::Ia, Jurisdiction::Nd]];
        yield 'example A123456789' => ['A123456789', [Jurisdiction::Ok]];
        yield 'example Y620-9305-7270' => ['Y620-9305-7270', [Jurisdiction::Il]];
        yield 'example Z123-456-78-901-0' => [
            'Z123-456-78-901-0',
            [
                Jurisdiction::Fl,
                Jurisdiction::Md,
                Jurisdiction::Mi,
            ],
        ];
        yield 'example MD12345678900' => ['MD12345678900', []];
        yield 'example H300-1987-0162-04' => ['H300-1987-0162-04', [Jurisdiction::Wi]];
        yield 'example A12345678901234' => ['A12345678901234', [Jurisdiction::Nj]];
        yield 'example WDL123456789' => ['WDL123456789', [Jurisdiction::Wa]];
        yield 'example GROSECM378NA' => ['GROSECM378NA', [Jurisdiction::Wa]];
        yield 'example SMITH**12*AB' => ['SMITH**12*AB', [Jurisdiction::Wa]];
        yield 'overlap: 6 alphanumerics, letters only' => ['abcdef', [Jurisdiction::Ks]];
        yield 'overlap: 7 digits + a letter of the VT set, lower case' => ['1234567x', [Jurisdiction::Vt]];
        yield 'overlap: 9 digits + a letter' => ['987654321Z', [Jurisdiction::Ms]];
        yield 'overlap: letter + 8 digits, NE letter V' => [
            'V12345678',
            [
                Jurisdiction::Az,
                Jurisdiction::Ia,
                Jurisdiction::Ky,
                Jurisdiction::Ne,
            ],
        ];
        yield 'overlap: letter + 8 digits, letter outside every set' => [
            'Z12345678',
            [
                Jurisdiction::Az,
                Jurisdiction::Ia,
                Jurisdiction::Ky,
            ],
        ];
        yield 'overlap: 9 alphanumerics, H first, not letter + 8 digits' => [
            'H1234567A',
            [
                Jurisdiction::Hi,
                Jurisdiction::Ia,
            ],
        ];
        yield 'overlap: 9 alphanumerics, no listed layout' => ['1A2B3C4D5', [Jurisdiction::Ia]];
        yield 'overlap: letter + 11 digits' => ['A12345678901', [Jurisdiction::Il]];
        yield 'overlap: 11 digits + a letter (IL ID card, ruling 1)' => ['12345678901A', [Jurisdiction::Il]];
        yield 'overlap: letter + 12 digits' => [
            'A123456789012',
            [
                Jurisdiction::Fl,
                Jurisdiction::Md,
                Jurisdiction::Mi,
            ],
        ];
        yield 'overlap: letter + 13 digits' => ['A1234567890123', [Jurisdiction::Wi]];
        yield 'overlap: WDL + 9 letters' => ['WDLABCDEFGHI', [Jurisdiction::Wa]];
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
     * @return iterable<string, array{string}>
     */
    public static function ambiguousNumbers(): iterable
    {
        foreach (self::matchingJurisdictions() as $label => [$number, $jurisdictions]) {
            if (\count($jurisdictions) > 1) {
                yield $label => [$number];
            }
        }
    }

    #[DataProvider('ambiguousNumbers')]
    public function testNeverPicksOneOfSeveralJurisdictions(string $number): void
    {
        try {
            DlIdNumberFactory::fromNumber($number);
        } catch (AmbiguousJurisdictionException $exception) {
            self::assertSame(DlIdNumberFactory::jurisdictionsMatching($number), $exception->jurisdictions());
            self::assertMessageHidesInput($number, $exception->getMessage());

            return;
        }

        self::fail('Expected an AmbiguousJurisdictionException.');
    }

    public function testNineDigitsListTheElevenJurisdictions(): void
    {
        $this->expectException(AmbiguousJurisdictionException::class);
        $this->expectExceptionMessage(
            'American DL/ID number fits several jurisdictions: US-AZ, US-CO, US-CT, US-IA, US-ID, US-KS, US-MS, '
            . 'US-NM, US-NY, US-OK, US-WY.',
        );

        DlIdNumberFactory::fromNumber('123456789');
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
        $licence = DlIdNumberFactory::fromNumber($number);

        self::assertInstanceOf($class, $licence);
        self::assertTrue($class::from($number)->equals($licence));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        yield 'five digits' => ['12345', 'Invalid American DL/ID number (5 characters).'];
        yield 'eleven digits' => ['12345678901', 'Invalid American DL/ID number (11 characters).'];
        yield 'sixteen digits' => ['1234567890123456', 'Invalid American DL/ID number (16 characters).'];
        yield 'thirteen digits, a length FL, MD and MI have' => [
            '1234567890123',
            'Invalid American DL/ID number (fits no jurisdiction).',
        ];
        yield 'MD customer ID' => ['MD12345678900', 'Invalid American DL/ID number (fits no jurisdiction).'];
        yield 'seven digits + B' => ['1234567B', 'Invalid American DL/ID number (fits no jurisdiction).'];
        yield 'empty' => ['', 'Invalid American DL/ID number (0 characters).'];
        yield 'pasted several times' => [
            \str_repeat('123456789 ', 5),
            'Invalid American DL/ID number (too long).',
        ];
    }

    #[DataProvider('numbersValidNowhere')]
    public function testRefusesANumberValidNowhereWithoutEchoingIt(string $number, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => DlIdNumberFactory::fromNumber($value),
            $number,
            $message,
        );
    }

    /**
     * @return iterable<string, array{Jurisdiction, class-string<AbstractDlIdNumber>, string}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'US-AL' => [Jurisdiction::Al, AlDlIdNumber::class, '1234567'];
        yield 'US-AZ' => [Jurisdiction::Az, AzDlIdNumber::class, 'A12345678'];
        yield 'US-CO' => [Jurisdiction::Co, CoDlIdNumber::class, '123456789'];
        yield 'US-CT' => [Jurisdiction::Ct, CtDlIdNumber::class, '123456789'];
        yield 'US-FL' => [Jurisdiction::Fl, FlDlIdNumber::class, 'Z123456789010'];
        yield 'US-HI' => [Jurisdiction::Hi, HiDlIdNumber::class, 'H12345678'];
        yield 'US-IA' => [Jurisdiction::Ia, IaDlIdNumber::class, 'A1B2C3D4E'];
        yield 'US-ID' => [Jurisdiction::Id, IdDlIdNumber::class, 'AB123456C'];
        yield 'US-IL' => [Jurisdiction::Il, IlDlIdNumber::class, 'Y62093057270'];
        yield 'US-IN' => [Jurisdiction::In, InDlIdNumber::class, '1234567890'];
        yield 'US-KS' => [Jurisdiction::Ks, KsDlIdNumber::class, 'K12345678'];
        yield 'US-KY' => [Jurisdiction::Ky, KyDlIdNumber::class, 'C21545427'];
        yield 'US-MA' => [Jurisdiction::Ma, MaDlIdNumber::class, 'S12345678'];
        yield 'US-MD' => [Jurisdiction::Md, MdDlIdNumber::class, 'A999999999999'];
        yield 'US-ME' => [Jurisdiction::Me, MeDlIdNumber::class, '1234567'];
        yield 'US-MI' => [Jurisdiction::Mi, MiDlIdNumber::class, 'A123456789012'];
        yield 'US-MS' => [Jurisdiction::Ms, MsDlIdNumber::class, '123456789A'];
        yield 'US-ND' => [Jurisdiction::Nd, NdDlIdNumber::class, 'ABC123456'];
        yield 'US-NE' => [Jurisdiction::Ne, NeDlIdNumber::class, 'A12345678'];
        yield 'US-NJ' => [Jurisdiction::Nj, NjDlIdNumber::class, 'A12345678901234'];
        yield 'US-NM' => [Jurisdiction::Nm, NmDlIdNumber::class, '123456789'];
        yield 'US-NY' => [Jurisdiction::Ny, NyDlIdNumber::class, '123456789'];
        yield 'US-OK' => [Jurisdiction::Ok, OkDlIdNumber::class, 'A123456789'];
        yield 'US-PA' => [Jurisdiction::Pa, PaDlIdNumber::class, '12345678'];
        yield 'US-RI' => [Jurisdiction::Ri, RiDlIdNumber::class, '12345678'];
        yield 'US-TX' => [Jurisdiction::Tx, TxDlIdNumber::class, '12345678'];
        yield 'US-VT' => [Jurisdiction::Vt, VtDlIdNumber::class, '3470128A'];
        yield 'US-WA' => [Jurisdiction::Wa, WaDlIdNumber::class, 'GROSECM378NA'];
        yield 'US-WI' => [Jurisdiction::Wi, WiDlIdNumber::class, 'H3001987016204'];
        yield 'US-WY' => [Jurisdiction::Wy, WyDlIdNumber::class, '123456789'];
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
        yield 'US-AK' => [Jurisdiction::Ak];
        yield 'US-AR' => [Jurisdiction::Ar];
        yield 'US-CA' => [Jurisdiction::Ca];
        yield 'US-DC' => [Jurisdiction::Dc];
        yield 'US-DE' => [Jurisdiction::De];
        yield 'US-GA' => [Jurisdiction::Ga];
        yield 'US-LA' => [Jurisdiction::La];
        yield 'US-MN' => [Jurisdiction::Mn];
        yield 'US-MO' => [Jurisdiction::Mo];
        yield 'US-MT' => [Jurisdiction::Mt];
        yield 'US-NC' => [Jurisdiction::Nc];
        yield 'US-NH' => [Jurisdiction::Nh];
        yield 'US-NV' => [Jurisdiction::Nv];
        yield 'US-OH' => [Jurisdiction::Oh];
        yield 'US-OR' => [Jurisdiction::Or];
        yield 'US-SC' => [Jurisdiction::Sc];
        yield 'US-SD' => [Jurisdiction::Sd];
        yield 'US-TN' => [Jurisdiction::Tn];
        yield 'US-UT' => [Jurisdiction::Ut];
        yield 'US-VA' => [Jurisdiction::Va];
        yield 'US-WV' => [Jurisdiction::Wv];
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
            static fn (string $value): mixed => DlIdNumberFactory::fromNumberIn($value, Jurisdiction::Ny),
            '12345678',
            'Invalid US-NY DL/ID number (8 characters).',
        );
    }

    private function compare(Jurisdiction $left, Jurisdiction $right): int
    {
        return \array_search($left, Jurisdiction::cases(), true) <=> \array_search($right, Jurisdiction::cases(), true);
    }
}
