<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CA;

use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\AbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\AbstractHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\BcHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\MbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NbHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NlHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NsHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NtHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\NuHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCard;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardVersionCode;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\PeHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\QcHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\SkHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\YtHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCardNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
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
 * Expectations follow docs/sources/CA.md "Health card numbers — overlap (the factory's test oracle)": every worked
 * example and one input per overlap-table row not already among them.
 */
#[CoversClass(HealthCardNumberFactory::class)]
#[UsesClass(AbstractHealthCardNumber::class)]
#[UsesClass(AbHealthCardNumber::class)]
#[UsesClass(BcHealthCardNumber::class)]
#[UsesClass(MbHealthCardNumber::class)]
#[UsesClass(NbHealthCardNumber::class)]
#[UsesClass(NlHealthCardNumber::class)]
#[UsesClass(NsHealthCardNumber::class)]
#[UsesClass(NtHealthCardNumber::class)]
#[UsesClass(NuHealthCardNumber::class)]
#[UsesClass(OnHealthCardNumber::class)]
#[UsesClass(OnHealthCard::class)]
#[UsesClass(OnHealthCardVersionCode::class)]
#[UsesClass(PeHealthCardNumber::class)]
#[UsesClass(QcHealthCardNumber::class)]
#[UsesClass(SkHealthCardNumber::class)]
#[UsesClass(YtHealthCardNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(AmbiguousJurisdictionException::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(SubdivisionResolver::class)]
#[UsesClass(Text::class)]
final class HealthCardNumberFactoryTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, list<Jurisdiction>}>
     */
    public static function matchingJurisdictions(): iterable
    {
        $nineDigits = [
            Jurisdiction::Ab,
            Jurisdiction::Mb,
            Jurisdiction::Nb,
            Jurisdiction::Nu,
            Jurisdiction::Sk,
            Jurisdiction::Yt,
        ];

        yield 'example 1234567' => ['1234567', []];
        yield 'example 12345678' => ['12345678', [Jurisdiction::Pe]];
        yield 'example 123456789' => ['123456789', $nineDigits];
        yield 'example 123 456 789' => ['123 456 789', $nineDigits];
        yield 'example 1234567890' => ['1234567890', [Jurisdiction::Ns]];
        yield 'example 1234567897' => ['1234567897', [Jurisdiction::Ns, Jurisdiction::On]];
        yield 'example 9876543217 (ON-IHCS)' => ['9876543217', [Jurisdiction::Ns, Jurisdiction::On]];
        yield 'example 9012372173 (BC-TP)' => ['9012372173', [Jurisdiction::Bc, Jurisdiction::Ns]];
        yield 'example 9012372174' => ['9012372174', [Jurisdiction::Ns, Jurisdiction::On]];
        yield 'example 9012372175' => ['9012372175', [Jurisdiction::Ns]];
        yield 'example 0123456789' => ['0123456789', [Jurisdiction::Ns]];
        yield 'example 5584486674 (AB-PRG illustration)' => ['5584486674', [Jurisdiction::Ns]];
        yield 'example 5584486674YM' => ['5584486674YM', []];
        yield 'example 9876543217YM (printed ON card)' => ['9876543217YM', []];
        yield 'example 9876543217 ym (printed ON card)' => ['9876543217 ym', []];
        yield 'example 9876543217A (printed ON card)' => ['9876543217A', []];
        yield 'example 9876543217ABC' => ['9876543217ABC', []];
        yield 'example 1234567890AB' => ['1234567890AB', []];
        yield 'example 123456789012' => ['123456789012', [Jurisdiction::Nl]];
        yield 'example N1234567' => ['N1234567', [Jurisdiction::Nt]];
        yield 'example H1234567' => ['H1234567', [Jurisdiction::Nt]];
        yield 'example A1234567' => ['A1234567', []];
        yield 'example BOUF 9401 1419' => ['BOUF 9401 1419', [Jurisdiction::Qc]];
        yield 'example BOUF94511419' => ['BOUF94511419', [Jurisdiction::Qc]];
        yield 'example YUXJ75021213' => ['YUXJ75021213', [Jurisdiction::Qc]];
        yield 'example BOUF94311419 (month 31)' => ['BOUF94311419', []];
        yield 'example BERC920401M2 (temporary NAM)' => ['BERC920401M2', []];
        yield 'overlap: 10 digits passing BC MOD-11 and ON MOD 10' => [
            '9282887125',
            [Jurisdiction::Bc, Jurisdiction::Ns, Jurisdiction::On],
        ];
        yield 'overlap: 10 digits passing BC MOD-11 and failing ON MOD 10' => [
            '9555555552',
            [Jurisdiction::Bc, Jurisdiction::Ns],
        ];
        yield 'overlap: 11 digits' => ['12345678901', []];
        yield 'overlap: 13 digits' => ['1234567890123', []];
        yield 'overlap: empty' => ['', []];
    }

    /**
     * @param list<Jurisdiction> $expected
     */
    #[DataProvider('matchingJurisdictions')]
    public function testListsMatchingJurisdictionsInEnumOrder(string $number, array $expected): void
    {
        self::assertSame($expected, HealthCardNumberFactory::jurisdictionsMatching($number));
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
            HealthCardNumberFactory::fromNumber($number);
        } catch (AmbiguousJurisdictionException $exception) {
            self::assertSame(HealthCardNumberFactory::jurisdictionsMatching($number), $exception->jurisdictions());
            self::assertMessageHidesInput($number, $exception->getMessage());

            return;
        }

        self::fail('Expected an AmbiguousJurisdictionException.');
    }

    public function testNineDigitsListTheSixJurisdictions(): void
    {
        $this->expectException(AmbiguousJurisdictionException::class);
        $this->expectExceptionMessage(
            'Canadian health card number fits several jurisdictions: CA-AB, CA-MB, CA-NB, CA-NU, CA-SK, CA-YT.',
        );

        HealthCardNumberFactory::fromNumber('123456789');
    }

    /**
     * @return iterable<string, array{string, class-string<AbstractHealthCardNumber>, string}>
     */
    public static function singleMatches(): iterable
    {
        yield '12345678' => ['12345678', PeHealthCardNumber::class, '12345678'];
        yield '1234567890' => ['1234567890', NsHealthCardNumber::class, '1234567890'];
        yield '9012372175' => ['9012372175', NsHealthCardNumber::class, '9012372175'];
        yield '0123456789' => ['0123456789', NsHealthCardNumber::class, '0123456789'];
        yield '5584486674' => ['5584486674', NsHealthCardNumber::class, '5584486674'];
        yield '123456789012' => ['123456789012', NlHealthCardNumber::class, '123456789012'];
        yield 'N1234567' => ['N1234567', NtHealthCardNumber::class, 'N1234567'];
        yield 'H1234567' => ['H1234567', NtHealthCardNumber::class, 'H1234567'];
        yield 'BOUF 9401 1419' => ['BOUF 9401 1419', QcHealthCardNumber::class, 'BOUF94011419'];
        yield 'BOUF94511419' => ['BOUF94511419', QcHealthCardNumber::class, 'BOUF94511419'];
        yield 'YUXJ75021213' => ['YUXJ75021213', QcHealthCardNumber::class, 'YUXJ75021213'];
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
     */
    #[DataProvider('singleMatches')]
    public function testReturnsTheOnlyMatchingJurisdiction(string $number, string $class, string $value): void
    {
        $card = HealthCardNumberFactory::fromNumber($number);

        self::assertInstanceOf($class, $card);
        self::assertSame($value, (string) $card);
        self::assertTrue($class::from($number)->equals($card));
    }

    public function testRefusesAnOntarioVersionCodeInsteadOfDroppingIt(): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => HealthCardNumberFactory::fromNumberIn($value, Jurisdiction::On),
            '9876543217 YM',
            'Invalid CA-ON health card number (version code: use fromCard()).',
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        $noJurisdiction = 'Invalid Canadian health card number (fits no jurisdiction).';
        $onHint = 'Invalid Canadian health card number (fits no jurisdiction; an Ontario card with a version code:'
            . ' use OnHealthCardNumber::fromCard()).';

        yield 'seven digits' => ['1234567', 'Invalid Canadian health card number (7 characters).'];
        yield 'eleven digits, an ON length' => ['12345678901', $noJurisdiction];
        yield 'thirteen characters' => ['9876543217ABC', 'Invalid Canadian health card number (13 characters).'];
        yield 'AB illustration with version code' => ['5584486674YM', $noJurisdiction];
        yield 'printed ON card with a version code' => ['9876543217 YM', $onHint];
        yield 'printed ON card with one letter' => ['9876543217A', $onHint];
        yield 'ten digits + two letters failing ON' => ['1234567890AB', $noJurisdiction];
        yield 'letter outside the NT set' => ['A1234567', $noJurisdiction];
        yield 'QC month 31' => ['BOUF94311419', $noJurisdiction];
        yield 'QC temporary NAM' => ['BERC920401M2', $noJurisdiction];
        yield 'empty' => ['', 'Invalid Canadian health card number (0 characters).'];
        yield 'pasted several times' => [
            \str_repeat('123456789 ', 5),
            'Invalid Canadian health card number (too long).',
        ];
    }

    #[DataProvider('numbersValidNowhere')]
    public function testRefusesANumberValidNowhereWithoutEchoingIt(string $number, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): mixed => HealthCardNumberFactory::fromNumber($value),
            $number,
            $message,
        );
    }

    /**
     * @return iterable<string, array{Jurisdiction, class-string<AbstractHealthCardNumber>, string}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'CA-AB' => [Jurisdiction::Ab, AbHealthCardNumber::class, '123456789'];
        yield 'CA-BC' => [Jurisdiction::Bc, BcHealthCardNumber::class, '9012372173'];
        yield 'CA-MB' => [Jurisdiction::Mb, MbHealthCardNumber::class, '123456789'];
        yield 'CA-NB' => [Jurisdiction::Nb, NbHealthCardNumber::class, '123456789'];
        yield 'CA-NL' => [Jurisdiction::Nl, NlHealthCardNumber::class, '123456789012'];
        yield 'CA-NS' => [Jurisdiction::Ns, NsHealthCardNumber::class, '9012372173'];
        yield 'CA-NT' => [Jurisdiction::Nt, NtHealthCardNumber::class, 'T1234567'];
        yield 'CA-NU' => [Jurisdiction::Nu, NuHealthCardNumber::class, '123456789'];
        yield 'CA-ON' => [Jurisdiction::On, OnHealthCardNumber::class, '9876543217'];
        yield 'CA-PE' => [Jurisdiction::Pe, PeHealthCardNumber::class, '12345678'];
        yield 'CA-QC' => [Jurisdiction::Qc, QcHealthCardNumber::class, 'YUXJ75021213'];
        yield 'CA-SK' => [Jurisdiction::Sk, SkHealthCardNumber::class, '123456789'];
        yield 'CA-YT' => [Jurisdiction::Yt, YtHealthCardNumber::class, '123456789'];
    }

    /**
     * @param class-string<AbstractHealthCardNumber> $class
     */
    #[DataProvider('concreteClasses')]
    public function testFromNumberInReturnsTheConcreteClass(
        Jurisdiction $jurisdiction,
        string $class,
        string $number,
    ): void {
        $card = HealthCardNumberFactory::fromNumberIn($number, $jurisdiction);

        self::assertInstanceOf($class, $card);
        self::assertSame($jurisdiction, $card->jurisdiction());
        self::assertSame($number, $card->value);
        self::assertContains($jurisdiction, HealthCardNumberFactory::jurisdictionsMatching($number));
    }

    public function testEveryJurisdictionIsCovered(): void
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
            static fn (string $value): mixed => HealthCardNumberFactory::fromNumberIn($value, Jurisdiction::Bc),
            '9876543217',
            'Invalid CA-BC health card number checksum.',
        );
    }
}
