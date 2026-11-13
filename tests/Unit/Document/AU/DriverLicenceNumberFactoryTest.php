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
use MSSTC4PHP\ValueObject\Documents\Exception\AmbiguousJurisdictionException;
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
#[UsesClass(AmbiguousJurisdictionException::class)]
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
        yield 'one digit' => ['7', [Jurisdiction::Act, Jurisdiction::Nt, Jurisdiction::Vic]];
        yield 'six digits' => [
            '000000',
            [
                Jurisdiction::Act,
                Jurisdiction::Nsw,
                Jurisdiction::Nt,
                Jurisdiction::Sa,
                Jurisdiction::Tas,
                Jurisdiction::Vic,
            ],
        ];
        yield 'seven digits (WA sample)' => [
            '6437772',
            [
                Jurisdiction::Act,
                Jurisdiction::Nsw,
                Jurisdiction::Nt,
                Jurisdiction::Tas,
                Jurisdiction::Vic,
                Jurisdiction::Wa,
            ],
        ];
        yield 'eight digits (NSW sample)' => [
            '01234567',
            [
                Jurisdiction::Act,
                Jurisdiction::Nsw,
                Jurisdiction::Nt,
                Jurisdiction::Qld,
                Jurisdiction::Tas,
                Jurisdiction::Vic,
            ],
        ];
        yield 'nine digits (Qld sample)' => [
            '123 456 789',
            [Jurisdiction::Act, Jurisdiction::Nt, Jurisdiction::Qld, Jurisdiction::Vic],
        ];
        yield 'ten digits (ACT sample)' => ['1234567890', [Jurisdiction::Act, Jurisdiction::Nt, Jurisdiction::Vic]];
        yield 'six characters with a letter' => ['s12345', [Jurisdiction::Nsw, Jurisdiction::Sa, Jurisdiction::Tas]];
        yield 'eight characters with a letter' => ['AB123456', [Jurisdiction::Nsw, Jurisdiction::Tas]];
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
            DriverLicenceNumberFactory::fromNumber($number);
        } catch (AmbiguousJurisdictionException $exception) {
            self::assertSame(DriverLicenceNumberFactory::jurisdictionsMatching($number), $exception->jurisdictions());
            self::assertMessageHidesInput($number, $exception->getMessage());

            return;
        }

        self::fail('Expected an AmbiguousJurisdictionException.');
    }

    public function testAmbiguityMessageListsTheIsoCodes(): void
    {
        $this->expectException(AmbiguousJurisdictionException::class);
        $this->expectExceptionMessage('Australian driver licence number fits several jurisdictions: AU-NSW, AU-TAS.');

        DriverLicenceNumberFactory::fromNumber('AB123456');
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function numbersValidNowhere(): iterable
    {
        yield 'eleven digits' => ['12345678901', 'Invalid Australian driver licence number (11 characters).'];
        yield 'nine characters with a letter, a length some jurisdiction has' => [
            'A12-345-678',
            'Invalid Australian driver licence number (fits no jurisdiction).',
        ];
        yield 'punctuation at a length every format allows' => [
            '12345.78',
            'Invalid Australian driver licence number (fits no jurisdiction).',
        ];
        yield 'empty' => ['', 'Invalid Australian driver licence number (0 characters).'];
        yield 'pasted several times' => [
            \str_repeat('1234567890 ', 5),
            'Invalid Australian driver licence number (too long).',
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
     * @return iterable<string, array{Jurisdiction, class-string<AbstractDriverLicenceNumber>}>
     */
    public static function concreteClasses(): iterable
    {
        yield 'ACT' => [Jurisdiction::Act, ActDriverLicenceNumber::class];
        yield 'NSW' => [Jurisdiction::Nsw, NswDriverLicenceNumber::class];
        yield 'NT' => [Jurisdiction::Nt, NtDriverLicenceNumber::class];
        yield 'Qld' => [Jurisdiction::Qld, QldDriverLicenceNumber::class];
        yield 'SA' => [Jurisdiction::Sa, SaDriverLicenceNumber::class];
        yield 'Tas' => [Jurisdiction::Tas, TasDriverLicenceNumber::class];
        yield 'Vic' => [Jurisdiction::Vic, VicDriverLicenceNumber::class];
        yield 'WA' => [Jurisdiction::Wa, WaDriverLicenceNumber::class];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('concreteClasses')]
    public function testFromNumberInReturnsTheConcreteClass(Jurisdiction $jurisdiction, string $class): void
    {
        $number = match ($jurisdiction) {
            Jurisdiction::Qld => '12345678',
            Jurisdiction::Wa => '6437772',
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
            static fn (string $value): mixed => DriverLicenceNumberFactory::fromNumberIn($value, Jurisdiction::Wa),
            '01234567',
            'Invalid AU-WA driver licence number (8 characters).',
        );
    }
}
