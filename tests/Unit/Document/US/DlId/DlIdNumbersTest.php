<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US\DlId;

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
use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/US.md "DL/ID numbers", "Per jurisdiction": every quoted shape and every official
 * example; the other numbers are made up to fit or miss a shape.
 */
#[CoversClass(AbstractDlIdNumber::class)]
#[CoversClass(AlDlIdNumber::class)]
#[CoversClass(AzDlIdNumber::class)]
#[CoversClass(CoDlIdNumber::class)]
#[CoversClass(CtDlIdNumber::class)]
#[CoversClass(FlDlIdNumber::class)]
#[CoversClass(HiDlIdNumber::class)]
#[CoversClass(IaDlIdNumber::class)]
#[CoversClass(IdDlIdNumber::class)]
#[CoversClass(IlDlIdNumber::class)]
#[CoversClass(InDlIdNumber::class)]
#[CoversClass(KsDlIdNumber::class)]
#[CoversClass(KyDlIdNumber::class)]
#[CoversClass(MaDlIdNumber::class)]
#[CoversClass(MdDlIdNumber::class)]
#[CoversClass(MeDlIdNumber::class)]
#[CoversClass(MiDlIdNumber::class)]
#[CoversClass(MsDlIdNumber::class)]
#[CoversClass(NdDlIdNumber::class)]
#[CoversClass(NeDlIdNumber::class)]
#[CoversClass(NjDlIdNumber::class)]
#[CoversClass(NmDlIdNumber::class)]
#[CoversClass(NyDlIdNumber::class)]
#[CoversClass(OkDlIdNumber::class)]
#[CoversClass(PaDlIdNumber::class)]
#[CoversClass(RiDlIdNumber::class)]
#[CoversClass(TxDlIdNumber::class)]
#[CoversClass(VtDlIdNumber::class)]
#[CoversClass(WaDlIdNumber::class)]
#[CoversClass(WiDlIdNumber::class)]
#[CoversClass(WyDlIdNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Text::class)]
final class DlIdNumbersTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    private const int LONGEST_TRIED = 16;

    /**
     * @return iterable<string, array{class-string<AbstractDlIdNumber>, string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'AL seven-digit licence' => [AlDlIdNumber::class, '1234567', '1234567'];
        yield 'AL eight-digit licence or ID card, leading zero kept' => [AlDlIdNumber::class, '01234567', '01234567'];
        yield 'AZ letter and eight digits, upper-cased' => [AzDlIdNumber::class, 'a12345678', 'A12345678'];
        yield 'AZ nine digits (an SSN still printed)' => [AzDlIdNumber::class, '123456789', '123456789'];
        yield 'CO nine digits' => [CoDlIdNumber::class, '123456789', '123456789'];
        yield 'CT nine digits' => [CtDlIdNumber::class, '012345678', '012345678'];
        yield 'FL official example with hyphens' => [FlDlIdNumber::class, 'Z123-456-78-901-0', 'Z123456789010'];
        yield 'FL official example without hyphens, upper-cased' => [
            FlDlIdNumber::class,
            'z123456789010',
            'Z123456789010',
        ];
        yield 'HI H and eight digits' => [HiDlIdNumber::class, 'H12345678', 'H12345678'];
        yield 'HI H and eight letters, upper-cased' => [HiDlIdNumber::class, 'habcdefgh', 'HABCDEFGH'];
        yield 'IA nine digits' => [IaDlIdNumber::class, '123456789', '123456789'];
        yield 'IA nine letters' => [IaDlIdNumber::class, 'ABCDEFGHI', 'ABCDEFGHI'];
        yield 'IA mixed, upper-cased' => [IaDlIdNumber::class, 'a1b2c3d4e', 'A1B2C3D4E'];
        yield 'ID current format' => [IdDlIdNumber::class, 'AB123456C', 'AB123456C'];
        yield 'ID pre-1993 nine digits' => [IdDlIdNumber::class, '123456789', '123456789'];
        yield 'IL official example as listed' => [IlDlIdNumber::class, 'Y620 930 57 270', 'Y62093057270'];
        yield 'IL licence with hyphens, upper-cased' => [IlDlIdNumber::class, 'y620-9305-7270', 'Y62093057270'];
        yield 'IL ID card (PID), letter last' => [IlDlIdNumber::class, '12345678901A', '12345678901A'];
        yield 'IN ten digits' => [InDlIdNumber::class, '1234567890', '1234567890'];
        yield 'KS official example' => [KsDlIdNumber::class, 'K12345678', 'K12345678'];
        yield 'KS nine digits (SSN)' => [KsDlIdNumber::class, '123456789', '123456789'];
        yield 'KS six alphanumerics, upper-cased' => [KsDlIdNumber::class, 'ab12c3', 'AB12C3'];
        yield 'KS six digits' => [KsDlIdNumber::class, '123456', '123456'];
        yield 'KY official example' => [KyDlIdNumber::class, 'C21545427', 'C21545427'];
        yield 'KY dashes removed, upper-cased' => [KyDlIdNumber::class, 'c21-545-427', 'C21545427'];
        yield 'MA S and eight digits' => [MaDlIdNumber::class, 'S12345678', 'S12345678'];
        yield 'MA SA and seven digits, upper-cased' => [MaDlIdNumber::class, 'sa1234567', 'SA1234567'];
        yield 'MD official field example' => [MdDlIdNumber::class, 'A999999999999', 'A999999999999'];
        yield 'ME seven digits' => [MeDlIdNumber::class, '1234567', '1234567'];
        yield 'MI letter and twelve digits' => [MiDlIdNumber::class, 'A123456789012', 'A123456789012'];
        yield 'MS nine digits' => [MsDlIdNumber::class, '123456789', '123456789'];
        yield 'MS Rule 4.4 spouse form, upper-cased' => [MsDlIdNumber::class, '123456789a', '123456789A'];
        yield 'ND three letters and six digits' => [NdDlIdNumber::class, 'ABC123456', 'ABC123456'];
        yield 'NE letter A' => [NeDlIdNumber::class, 'A12345678', 'A12345678'];
        yield 'NE letter V, upper-cased' => [NeDlIdNumber::class, 'v12345678', 'V12345678'];
        yield 'NJ letter and fourteen digits' => [NjDlIdNumber::class, 'A12345678901234', 'A12345678901234'];
        yield 'NM nine digits' => [NmDlIdNumber::class, '123456789', '123456789'];
        yield 'NY nine digits' => [NyDlIdNumber::class, '123456789', '123456789'];
        yield 'NY leading zero kept' => [NyDlIdNumber::class, '012345678', '012345678'];
        yield 'OK letter and nine digits' => [OkDlIdNumber::class, 'A123456789', 'A123456789'];
        yield 'OK nine digits on request' => [OkDlIdNumber::class, '123456789', '123456789'];
        yield 'PA eight digits' => [PaDlIdNumber::class, '12345678', '12345678'];
        yield 'RI seven digits' => [RiDlIdNumber::class, '1234567', '1234567'];
        yield 'RI eight digits' => [RiDlIdNumber::class, '12345678', '12345678'];
        yield 'TX eight digits' => [TxDlIdNumber::class, '12345678', '12345678'];
        yield 'VT official example digits only' => [VtDlIdNumber::class, '10512345', '10512345'];
        yield 'VT official example letter A' => [VtDlIdNumber::class, '3470128A', '3470128A'];
        yield 'VT official example letter V, upper-cased' => [VtDlIdNumber::class, '2054512v', '2054512V'];
        yield 'WA WDL format, upper-cased' => [WaDlIdNumber::class, 'wdl12345abcd', 'WDL12345ABCD'];
        yield 'WA legacy PIC official example A' => [WaDlIdNumber::class, 'GROSECM378NA', 'GROSECM378NA'];
        yield 'WA legacy PIC official example B' => [WaDlIdNumber::class, 'WEAVESE301C0', 'WEAVESE301C0'];
        yield 'WA legacy PIC with asterisks' => [WaDlIdNumber::class, 'SMITH**12*AB', 'SMITH**12*AB'];
        yield 'WI official example with hyphens' => [WiDlIdNumber::class, 'H300-1987-0162-04', 'H3001987016204'];
        yield 'WY six digits, hyphen, three digits' => [WyDlIdNumber::class, '123456-789', '123456789'];
        yield 'separators, tabs and NBSP' => [NyDlIdNumber::class, " 123-45\t67\u{00A0}89 ", '123456789'];
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     */
    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheNormalisedNumber(string $class, string $input, string $expected): void
    {
        $number = $class::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    /**
     * @return iterable<string, array{class-string<AbstractDlIdNumber>, Jurisdiction, string}>
     */
    public static function jurisdictions(): iterable
    {
        yield 'AL' => [AlDlIdNumber::class, Jurisdiction::Al, '1234567'];
        yield 'AZ' => [AzDlIdNumber::class, Jurisdiction::Az, 'A12345678'];
        yield 'CO' => [CoDlIdNumber::class, Jurisdiction::Co, '123456789'];
        yield 'CT' => [CtDlIdNumber::class, Jurisdiction::Ct, '123456789'];
        yield 'FL' => [FlDlIdNumber::class, Jurisdiction::Fl, 'Z123456789010'];
        yield 'HI' => [HiDlIdNumber::class, Jurisdiction::Hi, 'H12345678'];
        yield 'IA' => [IaDlIdNumber::class, Jurisdiction::Ia, '123456789'];
        yield 'ID' => [IdDlIdNumber::class, Jurisdiction::Id, 'AB123456C'];
        yield 'IL' => [IlDlIdNumber::class, Jurisdiction::Il, 'Y62093057270'];
        yield 'IN' => [InDlIdNumber::class, Jurisdiction::In, '1234567890'];
        yield 'KS' => [KsDlIdNumber::class, Jurisdiction::Ks, 'K12345678'];
        yield 'KY' => [KyDlIdNumber::class, Jurisdiction::Ky, 'C21545427'];
        yield 'MA' => [MaDlIdNumber::class, Jurisdiction::Ma, 'S12345678'];
        yield 'MD' => [MdDlIdNumber::class, Jurisdiction::Md, 'A999999999999'];
        yield 'ME' => [MeDlIdNumber::class, Jurisdiction::Me, '1234567'];
        yield 'MI' => [MiDlIdNumber::class, Jurisdiction::Mi, 'A123456789012'];
        yield 'MS' => [MsDlIdNumber::class, Jurisdiction::Ms, '123456789'];
        yield 'ND' => [NdDlIdNumber::class, Jurisdiction::Nd, 'ABC123456'];
        yield 'NE' => [NeDlIdNumber::class, Jurisdiction::Ne, 'A12345678'];
        yield 'NJ' => [NjDlIdNumber::class, Jurisdiction::Nj, 'A12345678901234'];
        yield 'NM' => [NmDlIdNumber::class, Jurisdiction::Nm, '123456789'];
        yield 'NY' => [NyDlIdNumber::class, Jurisdiction::Ny, '123456789'];
        yield 'OK' => [OkDlIdNumber::class, Jurisdiction::Ok, 'A123456789'];
        yield 'PA' => [PaDlIdNumber::class, Jurisdiction::Pa, '12345678'];
        yield 'RI' => [RiDlIdNumber::class, Jurisdiction::Ri, '1234567'];
        yield 'TX' => [TxDlIdNumber::class, Jurisdiction::Tx, '12345678'];
        yield 'VT' => [VtDlIdNumber::class, Jurisdiction::Vt, '10512345'];
        yield 'WA' => [WaDlIdNumber::class, Jurisdiction::Wa, 'WDL123456789'];
        yield 'WI' => [WiDlIdNumber::class, Jurisdiction::Wi, 'H3001987016204'];
        yield 'WY' => [WyDlIdNumber::class, Jurisdiction::Wy, '123456789'];
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     */
    #[DataProvider('jurisdictions')]
    public function testTellsItsJurisdiction(string $class, Jurisdiction $jurisdiction, string $sample): void
    {
        self::assertSame($jurisdiction, $class::from($sample)->jurisdiction());
    }

    public function testDifferentJurisdictionsAreNotEqual(): void
    {
        self::assertFalse(NyDlIdNumber::from('123456789')->equals(CoDlIdNumber::from('123456789')));
        self::assertTrue(NyDlIdNumber::from('123456789')->equals(NyDlIdNumber::from('123-456-789')));
    }

    /**
     * @return iterable<string, array{class-string<AbstractDlIdNumber>, non-empty-list<positive-int>}>
     */
    public static function lengths(): iterable
    {
        yield 'AL' => [AlDlIdNumber::class, [7, 8]];
        yield 'AZ' => [AzDlIdNumber::class, [9]];
        yield 'CO' => [CoDlIdNumber::class, [9]];
        yield 'CT' => [CtDlIdNumber::class, [9]];
        yield 'FL' => [FlDlIdNumber::class, [13]];
        yield 'HI' => [HiDlIdNumber::class, [9]];
        yield 'IA' => [IaDlIdNumber::class, [9]];
        yield 'ID' => [IdDlIdNumber::class, [9]];
        yield 'IL' => [IlDlIdNumber::class, [12]];
        yield 'IN' => [InDlIdNumber::class, [10]];
        yield 'KS' => [KsDlIdNumber::class, [6, 9]];
        yield 'KY' => [KyDlIdNumber::class, [9]];
        yield 'MA' => [MaDlIdNumber::class, [9]];
        yield 'MD' => [MdDlIdNumber::class, [13]];
        yield 'ME' => [MeDlIdNumber::class, [7]];
        yield 'MI' => [MiDlIdNumber::class, [13]];
        yield 'MS' => [MsDlIdNumber::class, [9, 10]];
        yield 'ND' => [NdDlIdNumber::class, [9]];
        yield 'NE' => [NeDlIdNumber::class, [9]];
        yield 'NJ' => [NjDlIdNumber::class, [15]];
        yield 'NM' => [NmDlIdNumber::class, [9]];
        yield 'NY' => [NyDlIdNumber::class, [9]];
        yield 'OK' => [OkDlIdNumber::class, [9, 10]];
        yield 'PA' => [PaDlIdNumber::class, [8]];
        yield 'RI' => [RiDlIdNumber::class, [7, 8]];
        yield 'TX' => [TxDlIdNumber::class, [8]];
        yield 'VT' => [VtDlIdNumber::class, [8]];
        yield 'WA' => [WaDlIdNumber::class, [12]];
        yield 'WI' => [WiDlIdNumber::class, [14]];
        yield 'WY' => [WyDlIdNumber::class, [9]];
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     * @param non-empty-list<positive-int> $lengths
     */
    #[DataProvider('lengths')]
    public function testRefusesEveryOtherLength(string $class, array $lengths): void
    {
        $iso = $class::from($this->sampleOf($class))->jurisdiction()->isoCode();
        for ($length = 0; $length <= self::LONGEST_TRIED; $length++) {
            $input = \str_repeat('1', $length);
            if (\in_array($length, $lengths, true)) {
                continue;
            }

            self::assertRefusedWithoutEcho(
                static fn (string $value): AbstractDlIdNumber => $class::from($value),
                $input,
                \sprintf('Invalid %s DL/ID number (wrong length).', $iso),
            );
        }
    }

    /**
     * @return iterable<string, array{class-string<AbstractDlIdNumber>, string, string}>
     */
    public static function invalidCharacters(): iterable
    {
        yield 'AL letter' => [AlDlIdNumber::class, '123456A', 'Invalid US-AL DL/ID number (invalid character).'];
        yield 'AZ two letters' => [AzDlIdNumber::class, 'AB2345678', 'Invalid US-AZ DL/ID number (invalid character).'];
        yield 'CO letter' => [CoDlIdNumber::class, '12345678A', 'Invalid US-CO DL/ID number (invalid character).'];
        yield 'CT letter' => [CtDlIdNumber::class, 'A12345678', 'Invalid US-CT DL/ID number (invalid character).'];
        yield 'FL two letters' => [
            FlDlIdNumber::class,
            'ZZ23456789012',
            'Invalid US-FL DL/ID number (invalid character).',
        ];
        yield 'FL digit first' => [
            FlDlIdNumber::class,
            '1234567890123',
            'Invalid US-FL DL/ID number (invalid character).',
        ];
        yield 'HI other first letter' => [
            HiDlIdNumber::class,
            'A12345678',
            'Invalid US-HI DL/ID number (invalid character).',
        ];
        yield 'HI asterisk' => [HiDlIdNumber::class, 'H1234567*', 'Invalid US-HI DL/ID number (invalid character).'];
        yield 'IA asterisk' => [IaDlIdNumber::class, '12345678*', 'Invalid US-IA DL/ID number (invalid character).'];
        yield 'ID one letter and eight digits' => [
            IdDlIdNumber::class,
            'A12345678',
            'Invalid US-ID DL/ID number (invalid character).',
        ];
        yield 'ID letter missing at the end' => [
            IdDlIdNumber::class,
            'AB1234567',
            'Invalid US-ID DL/ID number (invalid character).',
        ];
        yield 'IL twelve digits' => [
            IlDlIdNumber::class,
            '123456789012',
            'Invalid US-IL DL/ID number (invalid character).',
        ];
        yield 'IL letters at both ends' => [
            IlDlIdNumber::class,
            'Y6209305727A',
            'Invalid US-IL DL/ID number (invalid character).',
        ];
        yield 'IN letter' => [InDlIdNumber::class, '123456789A', 'Invalid US-IN DL/ID number (invalid character).'];
        yield 'KS other letter and eight digits' => [
            KsDlIdNumber::class,
            'A12345678',
            'Invalid US-KS DL/ID number (invalid character).',
        ];
        yield 'KS asterisk at six' => [
            KsDlIdNumber::class,
            '12345*',
            'Invalid US-KS DL/ID number (invalid character).',
        ];
        yield 'KY nine digits' => [KyDlIdNumber::class, '123456789', 'Invalid US-KY DL/ID number (invalid character).'];
        yield 'MA other letter' => [
            MaDlIdNumber::class,
            'A12345678',
            'Invalid US-MA DL/ID number (invalid character).',
        ];
        yield 'MA other second letter' => [
            MaDlIdNumber::class,
            'SB1234567',
            'Invalid US-MA DL/ID number (invalid character).',
        ];
        yield 'MD MD customer ID' => [
            MdDlIdNumber::class,
            'MD12345678900',
            'Invalid US-MD DL/ID number (invalid character).',
        ];
        yield 'MD thirteen digits' => [
            MdDlIdNumber::class,
            '1234567890123',
            'Invalid US-MD DL/ID number (invalid character).',
        ];
        yield 'ME letter' => [MeDlIdNumber::class, '123456A', 'Invalid US-ME DL/ID number (invalid character).'];
        yield 'MI two letters' => [
            MiDlIdNumber::class,
            'AB23456789012',
            'Invalid US-MI DL/ID number (invalid character).',
        ];
        yield 'MS two letters at ten' => [
            MsDlIdNumber::class,
            '12345678AB',
            'Invalid US-MS DL/ID number (invalid character).',
        ];
        yield 'MS letter at nine' => [
            MsDlIdNumber::class,
            '12345678A',
            'Invalid US-MS DL/ID number (invalid character).',
        ];
        yield 'ND two letters' => [NdDlIdNumber::class, 'AB1234567', 'Invalid US-ND DL/ID number (invalid character).'];
        yield 'NE letter outside the set' => [
            NeDlIdNumber::class,
            'D12345678',
            'Invalid US-NE DL/ID number (invalid character).',
        ];
        yield 'NE nine digits' => [NeDlIdNumber::class, '123456789', 'Invalid US-NE DL/ID number (invalid character).'];
        yield 'NJ fifteen digits' => [
            NjDlIdNumber::class,
            '123456789012345',
            'Invalid US-NJ DL/ID number (invalid character).',
        ];
        yield 'NM letter' => [NmDlIdNumber::class, '12345678A', 'Invalid US-NM DL/ID number (invalid character).'];
        yield 'NY letter' => [NyDlIdNumber::class, 'A12345678', 'Invalid US-NY DL/ID number (invalid character).'];
        yield 'OK ten digits' => [OkDlIdNumber::class, '1234567890', 'Invalid US-OK DL/ID number (invalid character).'];
        yield 'OK letter and eight digits' => [
            OkDlIdNumber::class,
            'A12345678',
            'Invalid US-OK DL/ID number (invalid character).',
        ];
        yield 'PA letter' => [PaDlIdNumber::class, '1234567A', 'Invalid US-PA DL/ID number (invalid character).'];
        yield 'RI letter' => [RiDlIdNumber::class, '123456A', 'Invalid US-RI DL/ID number (invalid character).'];
        yield 'TX letter' => [TxDlIdNumber::class, '1234567A', 'Invalid US-TX DL/ID number (invalid character).'];
        yield 'VT letter outside the set' => [
            VtDlIdNumber::class,
            '1234567B',
            'Invalid US-VT DL/ID number (invalid character).',
        ];
        yield 'VT letter first' => [VtDlIdNumber::class, 'A2345678', 'Invalid US-VT DL/ID number (invalid character).'];
        yield 'WA other prefix' => [
            WaDlIdNumber::class,
            'WDA123456789',
            'Invalid US-WA DL/ID number (invalid character).',
        ];
        yield 'WA asterisk in the WDL format' => [
            WaDlIdNumber::class,
            'WDL12345678*',
            'Invalid US-WA DL/ID number (invalid character).',
        ];
        yield 'WA PIC letter in the digit positions' => [
            WaDlIdNumber::class,
            'GROSECMA78NA',
            'Invalid US-WA DL/ID number (invalid character).',
        ];
        yield 'WI fourteen digits' => [
            WiDlIdNumber::class,
            '12345678901234',
            'Invalid US-WI DL/ID number (invalid character).',
        ];
        yield 'WY letter' => [WyDlIdNumber::class, '12345678A', 'Invalid US-WY DL/ID number (invalid character).'];
        yield 'NY non-ASCII letter' => [
            NyDlIdNumber::class,
            "12345678\u{00C9}",
            'Invalid US-NY DL/ID number (invalid character).',
        ];
        yield 'NY control character' => [
            NyDlIdNumber::class,
            "\x0012345678",
            'Invalid US-NY DL/ID number (invalid character).',
        ];
        yield 'NY other separator' => [
            NyDlIdNumber::class,
            '123.45678',
            'Invalid US-NY DL/ID number (invalid character).',
        ];
        yield 'NY pasted several times' => [
            NyDlIdNumber::class,
            \str_repeat('123456789 ', 5),
            'Invalid US-NY DL/ID number (wrong length).',
        ];
        yield 'NY empty' => [NyDlIdNumber::class, '', 'Invalid US-NY DL/ID number (wrong length).'];
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     */
    #[DataProvider('invalidCharacters')]
    public function testRefusesWithoutEchoingTheInput(string $class, string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractDlIdNumber => $class::from($value),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{class-string<AbstractDlIdNumber>, string, string}>
     */
    public static function maskedForms(): iterable
    {
        yield 'six characters keep three' => [KsDlIdNumber::class, 'AB1234', '***234'];
        yield 'seven characters keep three' => [MeDlIdNumber::class, '1234567', '****567'];
        yield 'eight characters keep three' => [PaDlIdNumber::class, '12345678', '*****678'];
        yield 'nine characters keep four' => [NyDlIdNumber::class, '123456789', '*****6789'];
        yield 'letters are masked too' => [NdDlIdNumber::class, 'ABC123456', '*****3456'];
        yield 'fifteen characters keep four' => [NjDlIdNumber::class, 'A12345678901234', '***********1234'];
        yield 'Florida in the printed layout' => [FlDlIdNumber::class, 'Z123456789010', '****-***-**-901-0'];
        yield 'Wisconsin in the printed layout' => [WiDlIdNumber::class, 'H3001987016204', '****-****-**62-04'];
        yield 'Wyoming in the printed layout' => [WyDlIdNumber::class, '123456789', '*****6-789'];
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     */
    #[DataProvider('maskedForms')]
    public function testMasksAllButTheLastCharacters(string $class, string $input, string $expected): void
    {
        self::assertSame($expected, $class::from($input)->masked());
    }

    public function testFormatsThePublishedDisplayForms(): void
    {
        self::assertSame('Z123-456-78-901-0', FlDlIdNumber::from('Z123456789010')->formatted());
        self::assertSame('H300-1987-0162-04', WiDlIdNumber::from('H3001987016204')->formatted());
        self::assertSame('123456-789', WyDlIdNumber::from('123456789')->formatted());
    }

    /**
     * @param class-string<AbstractDlIdNumber> $class
     */
    private function sampleOf(string $class): string
    {
        foreach (self::jurisdictions() as [$candidate, , $sample]) {
            if ($candidate === $class) {
                return $sample;
            }
        }

        self::fail('No sample for ' . $class);
    }
}
