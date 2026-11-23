<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\AbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\AbstractHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\BcHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\MbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NlHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NsHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NtHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NuHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\PeHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\QcHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\SkHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\YtHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\LengthProbe;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CA.md "Health card numbers — per jurisdiction": every quoted shape, the BC-TP and
 * ON-IHCS worked examples, the QC-GIU and QC-X samples and the decision bullets; the other numbers are made up to fit
 * or miss a shape (the extra BC and ON numbers are computed with the published algorithms).
 */
#[CoversClass(AbstractHealthCardNumber::class)]
#[CoversClass(AbHealthCardNumber::class)]
#[CoversClass(BcHealthCardNumber::class)]
#[CoversClass(MbHealthCardNumber::class)]
#[CoversClass(NbHealthCardNumber::class)]
#[CoversClass(NlHealthCardNumber::class)]
#[CoversClass(NsHealthCardNumber::class)]
#[CoversClass(NtHealthCardNumber::class)]
#[CoversClass(NuHealthCardNumber::class)]
#[CoversClass(OnHealthCardNumber::class)]
#[CoversClass(PeHealthCardNumber::class)]
#[CoversClass(QcHealthCardNumber::class)]
#[CoversClass(SkHealthCardNumber::class)]
#[CoversClass(YtHealthCardNumber::class)]
#[UsesClass(LengthProbe::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Text::class)]
final class HealthCardNumbersTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    private const int LONGEST_TRIED = 16;

    /**
     * @return iterable<string, array{class-string<AbstractHealthCardNumber>, string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'AB nine digits' => [AbHealthCardNumber::class, '123 456 789', '123456789'];
        yield 'BC Teleplan example' => [BcHealthCardNumber::class, '9012372173', '9012372173'];
        yield 'BC check digit 8' => [BcHealthCardNumber::class, '9876543218', '9876543218'];
        yield 'BC check digit 7' => [BcHealthCardNumber::class, '9111111117', '9111111117'];
        yield 'BC check digit 2' => [BcHealthCardNumber::class, '9555555552', '9555555552'];
        yield 'BC every weight used' => [BcHealthCardNumber::class, '9246-813-578', '9246813578'];
        yield 'MB PHIN' => [MbHealthCardNumber::class, '123456789', '123456789'];
        yield 'NB nine digits' => [NbHealthCardNumber::class, '012345678', '012345678'];
        yield 'NL twelve digits' => [NlHealthCardNumber::class, '123456789012', '123456789012'];
        yield 'NS ten digits' => [NsHealthCardNumber::class, '1234567890', '1234567890'];
        yield 'NS leading zero kept' => [NsHealthCardNumber::class, '0123456789', '0123456789'];
        yield 'NT letter D' => [NtHealthCardNumber::class, 'D1234567', 'D1234567'];
        yield 'NT letter N' => [NtHealthCardNumber::class, 'N1234567', 'N1234567'];
        yield 'NT letter H (INFOBulletin 220902), upper-cased' => [NtHealthCardNumber::class, 'h1234567', 'H1234567'];
        yield 'NT letter M' => [NtHealthCardNumber::class, 'M1234567', 'M1234567'];
        yield 'NT letter T' => [NtHealthCardNumber::class, 'T1234567', 'T1234567'];
        yield 'NU nine digits' => [NuHealthCardNumber::class, '123456789', '123456789'];
        yield 'ON IHCS example' => [OnHealthCardNumber::class, '9876543217', '9876543217'];
        yield 'ON spaced' => [OnHealthCardNumber::class, '9876-543-217', '9876543217'];
        yield 'ON check digit 7' => [OnHealthCardNumber::class, '1234567897', '1234567897'];
        yield 'ON check digit 3' => [OnHealthCardNumber::class, '9123456783', '9123456783'];
        yield 'ON check digit 5' => [OnHealthCardNumber::class, '5555555555', '5555555555'];
        yield 'ON check digit 9' => [OnHealthCardNumber::class, '2468135799', '2468135799'];
        yield 'ON check digit 0 (unit position 0 read as Luhn 0)' => [
            OnHealthCardNumber::class,
            '1000000040',
            '1000000040',
        ];
        yield 'PE eight digits' => [PeHealthCardNumber::class, '12345678', '12345678'];
        yield 'QC GIU example' => [QcHealthCardNumber::class, 'YUXJ75021213', 'YUXJ75021213'];
        yield 'QC X leaflet, spaced' => [QcHealthCardNumber::class, 'BOUF 9401 1419', 'BOUF94011419'];
        yield 'QC X leaflet, female' => [QcHealthCardNumber::class, 'bouf94511419', 'BOUF94511419'];
        yield 'QC male month 01, day 01' => [QcHealthCardNumber::class, 'ABCD00010100', 'ABCD00010100'];
        yield 'QC male month 12, day 31' => [QcHealthCardNumber::class, 'ABCD00123100', 'ABCD00123100'];
        yield 'QC female month 51' => [QcHealthCardNumber::class, 'ABCD00510100', 'ABCD00510100'];
        yield 'QC female month 62' => [QcHealthCardNumber::class, 'ABCD00623100', 'ABCD00623100'];
        yield 'SK nine digits' => [SkHealthCardNumber::class, '123456789', '123456789'];
        yield 'YT nine digits' => [YtHealthCardNumber::class, '123456789', '123456789'];
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
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
     * @return iterable<string, array{class-string<AbstractHealthCardNumber>, Jurisdiction, string}>
     */
    public static function jurisdictions(): iterable
    {
        yield 'AB' => [AbHealthCardNumber::class, Jurisdiction::AB, '123456789'];
        yield 'BC' => [BcHealthCardNumber::class, Jurisdiction::BC, '9012372173'];
        yield 'MB' => [MbHealthCardNumber::class, Jurisdiction::MB, '123456789'];
        yield 'NB' => [NbHealthCardNumber::class, Jurisdiction::NB, '123456789'];
        yield 'NL' => [NlHealthCardNumber::class, Jurisdiction::NL, '123456789012'];
        yield 'NS' => [NsHealthCardNumber::class, Jurisdiction::NS, '1234567890'];
        yield 'NT' => [NtHealthCardNumber::class, Jurisdiction::NT, 'N1234567'];
        yield 'NU' => [NuHealthCardNumber::class, Jurisdiction::NU, '123456789'];
        yield 'ON' => [OnHealthCardNumber::class, Jurisdiction::ON, '9876543217'];
        yield 'PE' => [PeHealthCardNumber::class, Jurisdiction::PE, '12345678'];
        yield 'QC' => [QcHealthCardNumber::class, Jurisdiction::QC, 'YUXJ75021213'];
        yield 'SK' => [SkHealthCardNumber::class, Jurisdiction::SK, '123456789'];
        yield 'YT' => [YtHealthCardNumber::class, Jurisdiction::YT, '123456789'];
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
     */
    #[DataProvider('jurisdictions')]
    public function testTellsItsJurisdiction(string $class, Jurisdiction $jurisdiction, string $sample): void
    {
        self::assertSame($jurisdiction, $class::from($sample)->jurisdiction());
    }

    public function testDifferentJurisdictionsAreNotEqual(): void
    {
        self::assertFalse(AbHealthCardNumber::from('123456789')->equals(SkHealthCardNumber::from('123456789')));
        self::assertTrue(AbHealthCardNumber::from('123456789')->equals(AbHealthCardNumber::from('123-456-789')));
    }

    /**
     * @return iterable<string, array{class-string<AbstractHealthCardNumber>, non-empty-list<positive-int>}>
     */
    public static function lengths(): iterable
    {
        yield 'AB' => [AbHealthCardNumber::class, [9]];
        yield 'BC' => [BcHealthCardNumber::class, [10]];
        yield 'MB' => [MbHealthCardNumber::class, [9]];
        yield 'NB' => [NbHealthCardNumber::class, [9]];
        yield 'NL' => [NlHealthCardNumber::class, [12]];
        yield 'NS' => [NsHealthCardNumber::class, [10]];
        yield 'NT' => [NtHealthCardNumber::class, [8]];
        yield 'NU' => [NuHealthCardNumber::class, [9]];
        yield 'ON' => [OnHealthCardNumber::class, [10, 11, 12]];
        yield 'PE' => [PeHealthCardNumber::class, [8]];
        yield 'QC' => [QcHealthCardNumber::class, [12]];
        yield 'SK' => [SkHealthCardNumber::class, [9]];
        yield 'YT' => [YtHealthCardNumber::class, [9]];
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
     * @param non-empty-list<positive-int> $lengths
     */
    #[DataProvider('lengths')]
    public function testRefusesEveryOtherLength(string $class, array $lengths): void
    {
        $iso = $class::from($this->sampleOf($class))->jurisdiction()->isoCode();
        for ($length = 0; $length <= self::LONGEST_TRIED; $length++) {
            $input = \str_repeat('1', $length);
            self::assertSame(\in_array($length, $lengths, true), LengthProbe::fits($class, $input));
            if (\in_array($length, $lengths, true)) {
                continue;
            }

            self::assertRefusedWithoutEcho(
                static fn (string $value): AbstractHealthCardNumber => $class::from($value),
                $input,
                \sprintf('Invalid %s health card number (wrong length).', $iso),
            );
        }
    }

    /**
     * @return iterable<string, array{class-string<AbstractHealthCardNumber>, string, string}>
     */
    public static function refusedNumbers(): iterable
    {
        $character = 'health card number (invalid character).';
        $checksum = 'health card number checksum.';
        $birthDate = 'health card number (invalid birth date).';
        $code = 'health card number (version code: use fromCard()).';

        yield 'AB letter' => [AbHealthCardNumber::class, '12345678A', 'Invalid CA-AB ' . $character];
        yield 'BC without the leading 9' => [BcHealthCardNumber::class, '8012372173', 'Invalid CA-BC ' . $character];
        yield 'BC wrong check digit' => [BcHealthCardNumber::class, '9012372174', 'Invalid CA-BC ' . $checksum];
        yield 'BC check result 11' => [BcHealthCardNumber::class, '9123456780', 'Invalid CA-BC ' . $checksum];
        yield 'BC check result 10' => [BcHealthCardNumber::class, '9135792460', 'Invalid CA-BC ' . $checksum];
        yield 'BC ON example' => [BcHealthCardNumber::class, '9876543217', 'Invalid CA-BC ' . $checksum];
        yield 'NL letter' => [NlHealthCardNumber::class, '1234567890AB', 'Invalid CA-NL ' . $character];
        yield 'NT letter outside the set' => [NtHealthCardNumber::class, 'A1234567', 'Invalid CA-NT ' . $character];
        yield 'NT eight digits' => [NtHealthCardNumber::class, '12345678', 'Invalid CA-NT ' . $character];
        yield 'ON leading zero' => [OnHealthCardNumber::class, '0123456789', 'Invalid CA-ON ' . $character];
        yield 'ON digit as version code' => [OnHealthCardNumber::class, '98765432171', 'Invalid CA-ON ' . $character];
        yield 'ON wrong check digit' => [OnHealthCardNumber::class, '9876543218', 'Invalid CA-ON ' . $checksum];
        yield 'ON BC example' => [OnHealthCardNumber::class, '9012372173', 'Invalid CA-ON ' . $checksum];
        yield 'ON two-letter version code' => [OnHealthCardNumber::class, '9876543217YM', 'Invalid CA-ON ' . $code];
        yield 'ON lower-case version code' => [OnHealthCardNumber::class, '9876543217 ym', 'Invalid CA-ON ' . $code];
        yield 'ON one-letter version code' => [OnHealthCardNumber::class, '9876543217A', 'Invalid CA-ON ' . $code];
        yield 'ON AB illustration with version code' => [
            OnHealthCardNumber::class,
            '5584486674YM',
            'Invalid CA-ON ' . $code,
        ];
        yield 'ON three-letter version code' => [
            OnHealthCardNumber::class,
            '9876543217ABC',
            'Invalid CA-ON health card number (wrong length).',
        ];
        yield 'QC temporary NAM' => [QcHealthCardNumber::class, 'BERC920401M2', 'Invalid CA-QC ' . $character];
        yield 'QC three letters' => [QcHealthCardNumber::class, 'BOU194011419', 'Invalid CA-QC ' . $character];
        yield 'QC month 00' => [QcHealthCardNumber::class, 'ABCD00000100', 'Invalid CA-QC ' . $birthDate];
        yield 'QC month 13' => [QcHealthCardNumber::class, 'ABCD00130100', 'Invalid CA-QC ' . $birthDate];
        yield 'QC month 31' => [QcHealthCardNumber::class, 'BOUF94311419', 'Invalid CA-QC ' . $birthDate];
        yield 'QC month 50' => [QcHealthCardNumber::class, 'ABCD00500100', 'Invalid CA-QC ' . $birthDate];
        yield 'QC month 63' => [QcHealthCardNumber::class, 'ABCD00630100', 'Invalid CA-QC ' . $birthDate];
        yield 'QC day 00' => [QcHealthCardNumber::class, 'ABCD00010000', 'Invalid CA-QC ' . $birthDate];
        yield 'QC day 32' => [QcHealthCardNumber::class, 'ABCD00513200', 'Invalid CA-QC ' . $birthDate];
        yield 'SK other separator' => [SkHealthCardNumber::class, '123.45678', 'Invalid CA-SK ' . $character];
        yield 'SK pasted several times' => [
            SkHealthCardNumber::class,
            \str_repeat('123456789 ', 5),
            'Invalid CA-SK health card number (wrong length).',
        ];
        yield 'PE empty' => [PeHealthCardNumber::class, '', 'Invalid CA-PE health card number (wrong length).'];
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
     */
    #[DataProvider('refusedNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $class, string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractHealthCardNumber => $class::from($value),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{class-string<AbstractHealthCardNumber>, string, string}>
     */
    public static function maskedForms(): iterable
    {
        yield 'eight characters keep three' => [PeHealthCardNumber::class, '12345678', '*****678'];
        yield 'NT letter is masked' => [NtHealthCardNumber::class, 'N1234567', '*****567'];
        yield 'nine characters keep four' => [AbHealthCardNumber::class, '123456789', '*****6789'];
        yield 'ON ten digits keep four' => [OnHealthCardNumber::class, '9876543217', '******3217'];
        yield 'QC twelve characters keep four' => [QcHealthCardNumber::class, 'BOUF94011419', '********1419'];
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
     */
    #[DataProvider('maskedForms')]
    public function testMasksAllButTheLastCharacters(string $class, string $input, string $expected): void
    {
        self::assertSame($expected, $class::from($input)->masked());
    }

    /**
     * @return iterable<string, array{string, Sex, int, int, string}>
     */
    public static function encodedQuebecData(): iterable
    {
        yield 'GIU example' => ['YUXJ75021213', Sex::Male, 12, 2, '75'];
        yield 'X leaflet, male' => ['BOUF94011419', Sex::Male, 14, 1, '94'];
        yield 'X leaflet, female' => ['BOUF94511419', Sex::Female, 14, 1, '94'];
        yield 'male, December 31' => ['ABCD00123100', Sex::Male, 31, 12, '00'];
        yield 'female, first month' => ['ABCD09510100', Sex::Female, 1, 1, '09'];
        yield 'female, December 31' => ['ABCD99623100', Sex::Female, 31, 12, '99'];
    }

    #[DataProvider('encodedQuebecData')]
    public function testDecodesTheQuebecSexAndBirthFields(
        string $input,
        Sex $sex,
        int $day,
        int $month,
        string $yearDigits,
    ): void {
        $number = QcHealthCardNumber::from($input);

        self::assertSame($sex, $number->sex());
        self::assertSame($day, $number->birthDay());
        self::assertSame($month, $number->birthMonth());
        self::assertSame($yearDigits, $number->birthYearDigits());
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
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
