<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\AbDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\BcDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\NsDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\OnDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\QcDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicence\SkDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
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
 * Expectations follow docs/sources/CA.md "Driver's licence numbers — per jurisdiction": every quoted shape, every
 * official sample and the decision bullets (BC and SK zero padding, NS separators and DD/MM checks); the other numbers
 * are made up to fit or miss a shape.
 */
#[CoversClass(AbstractDriverLicenceNumber::class)]
#[CoversClass(AbDriverLicenceNumber::class)]
#[CoversClass(BcDriverLicenceNumber::class)]
#[CoversClass(NsDriverLicenceNumber::class)]
#[CoversClass(OnDriverLicenceNumber::class)]
#[CoversClass(QcDriverLicenceNumber::class)]
#[CoversClass(SkDriverLicenceNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Text::class)]
final class DriverLicenceNumbersTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    private const int LONGEST_TRIED = 16;

    /**
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'AB nine digits' => [AbDriverLicenceNumber::class, '123456789', '123456789'];
        yield 'AB display form' => [AbDriverLicenceNumber::class, '123456-789', '123456789'];
        yield 'BC new number (ICBC sample)' => [BcDriverLicenceNumber::class, '31098794', '31098794'];
        yield 'BC integer form padded to eight' => [BcDriverLicenceNumber::class, '347631', '00347631'];
        yield 'BC seven-digit string form padded' => [BcDriverLicenceNumber::class, '0347631', '00347631'];
        yield 'BC eight-digit string form kept' => [BcDriverLicenceNumber::class, '00347631', '00347631'];
        yield 'BC seven-digit card' => [BcDriverLicenceNumber::class, '1234567', '01234567'];
        yield 'BC one digit' => [BcDriverLicenceNumber::class, '1', '00000001'];
        yield 'NS official example with hyphens' => [
            NsDriverLicenceNumber::class,
            'FINCH--220867001',
            'FINCH220867001',
        ];
        yield 'NS short surname padded with spaces' => [NsDriverLicenceNumber::class, 'FOY  220867001', 'FOY220867001'];
        yield 'NS lower case, spaced' => [NsDriverLicenceNumber::class, 'finch 220867 001', 'FINCH220867001'];
        yield 'NS one letter, first day of January' => [NsDriverLicenceNumber::class, 'A010100001', 'A010100001'];
        yield 'NS last day, December' => [NsDriverLicenceNumber::class, 'ABCD311299001', 'ABCD311299001'];
        yield 'ON glossary sample with hyphens' => [
            OnDriverLicenceNumber::class,
            'X0915-26435-95718',
            'X09152643595718',
        ];
        yield 'ON glossary sample as displayed' => [OnDriverLicenceNumber::class, 'X09152643595718', 'X09152643595718'];
        yield 'ON letters only, upper-cased' => [OnDriverLicenceNumber::class, 'abcdefghijklmno', 'ABCDEFGHIJKLMNO'];
        yield 'ON digits only' => [OnDriverLicenceNumber::class, '123456789012345', '123456789012345'];
        yield 'QC card guide sample' => [QcDriverLicenceNumber::class, 'L1531-171274-08', 'L153117127408'];
        yield 'QC compact, upper-cased' => [QcDriverLicenceNumber::class, 'l153117127408', 'L153117127408'];
        yield 'SK sample card' => [SkDriverLicenceNumber::class, '35890562', '35890562'];
        yield 'SK temporary card without leading zeros' => [SkDriverLicenceNumber::class, '890562', '00890562'];
        yield 'SK leading zeros kept' => [SkDriverLicenceNumber::class, '00890562', '00890562'];
        yield 'separators, tabs and NBSP' => [AbDriverLicenceNumber::class, " 123-45\t67\u{00A0}89 ", '123456789'];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
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
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, Jurisdiction, string}>
     */
    public static function jurisdictions(): iterable
    {
        yield 'AB' => [AbDriverLicenceNumber::class, Jurisdiction::Ab, '123456789'];
        yield 'BC' => [BcDriverLicenceNumber::class, Jurisdiction::Bc, '31098794'];
        yield 'NS' => [NsDriverLicenceNumber::class, Jurisdiction::Ns, 'FINCH220867001'];
        yield 'ON' => [OnDriverLicenceNumber::class, Jurisdiction::On, 'X09152643595718'];
        yield 'QC' => [QcDriverLicenceNumber::class, Jurisdiction::Qc, 'L153117127408'];
        yield 'SK' => [SkDriverLicenceNumber::class, Jurisdiction::Sk, '35890562'];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('jurisdictions')]
    public function testTellsItsJurisdiction(string $class, Jurisdiction $jurisdiction, string $sample): void
    {
        self::assertSame($jurisdiction, $class::from($sample)->jurisdiction());
    }

    public function testPaddedAndUnpaddedFormsAreOneNumber(): void
    {
        self::assertTrue(BcDriverLicenceNumber::from('347631')->equals(BcDriverLicenceNumber::from('00347631')));
        self::assertTrue(SkDriverLicenceNumber::from('890562')->equals(SkDriverLicenceNumber::from('00890562')));
        self::assertFalse(BcDriverLicenceNumber::from('890562')->equals(SkDriverLicenceNumber::from('890562')));
    }

    /**
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, non-empty-list<positive-int>}>
     */
    public static function lengths(): iterable
    {
        yield 'AB' => [AbDriverLicenceNumber::class, [9]];
        yield 'BC' => [BcDriverLicenceNumber::class, [1, 2, 3, 4, 5, 6, 7, 8]];
        yield 'NS' => [NsDriverLicenceNumber::class, [10, 11, 12, 13, 14]];
        yield 'ON' => [OnDriverLicenceNumber::class, [15]];
        yield 'QC' => [QcDriverLicenceNumber::class, [13]];
        yield 'SK' => [SkDriverLicenceNumber::class, [1, 2, 3, 4, 5, 6, 7, 8]];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
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
                static fn (string $value): AbstractDriverLicenceNumber => $class::from($value),
                $input,
                \sprintf("Invalid %s driver's licence number (wrong length).", $iso),
            );
        }
    }

    /**
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function refusedNumbers(): iterable
    {
        $character = "driver's licence number (invalid character).";
        $birthDate = "driver's licence number (invalid birth date).";

        yield 'AB letter' => [AbDriverLicenceNumber::class, '12345678A', 'Invalid CA-AB ' . $character];
        yield 'BC letter' => [BcDriverLicenceNumber::class, '1234567A', 'Invalid CA-BC ' . $character];
        yield 'SK letter' => [SkDriverLicenceNumber::class, 'A1234567', 'Invalid CA-SK ' . $character];
        yield 'NS digit in the surname part' => [
            NsDriverLicenceNumber::class,
            '1INCH220867001',
            'Invalid CA-NS ' . $character,
        ];
        yield 'NS letter in the digits' => [
            NsDriverLicenceNumber::class,
            'FINCH22086700A',
            'Invalid CA-NS ' . $character,
        ];
        yield 'NS six letters' => [NsDriverLicenceNumber::class, 'FINCHE22086700', 'Invalid CA-NS ' . $character];
        yield 'NS all digits' => [NsDriverLicenceNumber::class, '1234567890', 'Invalid CA-NS ' . $character];
        yield 'NS apostrophe' => [NsDriverLicenceNumber::class, "O'NEI220867001", 'Invalid CA-NS ' . $character];
        yield 'NS day 00' => [NsDriverLicenceNumber::class, 'FINCH000867001', 'Invalid CA-NS ' . $birthDate];
        yield 'NS day 32' => [NsDriverLicenceNumber::class, 'FINCH320867001', 'Invalid CA-NS ' . $birthDate];
        yield 'NS month 00' => [NsDriverLicenceNumber::class, 'FINCH220067001', 'Invalid CA-NS ' . $birthDate];
        yield 'NS month 13' => [NsDriverLicenceNumber::class, 'FINCH221367001', 'Invalid CA-NS ' . $birthDate];
        yield 'NS one letter, month 13' => [NsDriverLicenceNumber::class, 'A011300001', 'Invalid CA-NS ' . $birthDate];
        yield 'ON asterisk' => [OnDriverLicenceNumber::class, 'X0915264359571*', 'Invalid CA-ON ' . $character];
        yield 'ON non-ASCII letter' => [
            OnDriverLicenceNumber::class,
            "X0915264359571\u{00C9}",
            'Invalid CA-ON ' . $character,
        ];
        yield 'QC two letters' => [QcDriverLicenceNumber::class, 'LL53117127408', 'Invalid CA-QC ' . $character];
        yield 'QC digit first' => [QcDriverLicenceNumber::class, '1153117127408', 'Invalid CA-QC ' . $character];
        yield 'AB other separator' => [AbDriverLicenceNumber::class, '123.45678', 'Invalid CA-AB ' . $character];
        yield 'AB pasted several times' => [
            AbDriverLicenceNumber::class,
            \str_repeat('123456789 ', 5),
            "Invalid CA-AB driver's licence number (wrong length).",
        ];
        yield 'BC empty' => [BcDriverLicenceNumber::class, '', "Invalid CA-BC driver's licence number (wrong length)."];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('refusedNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $class, string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractDriverLicenceNumber => $class::from($value),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function maskedForms(): iterable
    {
        yield 'BC padded to eight keeps three' => [BcDriverLicenceNumber::class, '347631', '*****631'];
        yield 'SK eight characters keep three' => [SkDriverLicenceNumber::class, '35890562', '*****562'];
        yield 'NS ten characters keep four' => [NsDriverLicenceNumber::class, 'A010100001', '******0001'];
        yield 'NS letters are masked too' => [NsDriverLicenceNumber::class, 'FINCH220867001', '**********7001'];
        yield 'ON fifteen characters keep four' => [OnDriverLicenceNumber::class, 'X09152643595718', '***********5718'];
        yield 'AB in the display layout' => [AbDriverLicenceNumber::class, '123456789', '*****6-789'];
        yield 'QC in the display layout' => [QcDriverLicenceNumber::class, 'L153117127408', '*****-****74-08'];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('maskedForms')]
    public function testMasksAllButTheLastCharacters(string $class, string $input, string $expected): void
    {
        self::assertSame($expected, $class::from($input)->masked());
    }

    public function testFormatsThePublishedDisplayForms(): void
    {
        self::assertSame('123456-789', AbDriverLicenceNumber::from('123456789')->formatted());
        self::assertSame('L1531-171274-08', QcDriverLicenceNumber::from('L153117127408')->formatted());
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
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
