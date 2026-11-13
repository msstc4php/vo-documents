<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\AU\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\AbstractDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\ActDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NswDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\NtDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\QldDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\SaDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\TasDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\VicDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicence\WaDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/AU.md "Driving licence numbers": the Home Affairs DVS field rules and the sample
 * licences printed on that page.
 */
#[CoversClass(AbstractDriverLicenceNumber::class)]
#[CoversClass(ActDriverLicenceNumber::class)]
#[CoversClass(NswDriverLicenceNumber::class)]
#[CoversClass(NtDriverLicenceNumber::class)]
#[CoversClass(QldDriverLicenceNumber::class)]
#[CoversClass(SaDriverLicenceNumber::class)]
#[CoversClass(TasDriverLicenceNumber::class)]
#[CoversClass(VicDriverLicenceNumber::class)]
#[CoversClass(WaDriverLicenceNumber::class)]
#[UsesClass(Jurisdiction::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Text::class)]
final class DriverLicenceNumbersTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'ACT official sample' => [ActDriverLicenceNumber::class, '1234567890', '1234567890'];
        yield 'ACT one digit' => [ActDriverLicenceNumber::class, '7', '7'];
        yield 'NSW official sample, leading zero kept' => [NswDriverLicenceNumber::class, '01234567', '01234567'];
        yield 'NSW six alphanumerics, upper-cased' => [NswDriverLicenceNumber::class, 'ab12c9', 'AB12C9'];
        yield 'NT official sample A' => [NtDriverLicenceNumber::class, '1564102', '1564102'];
        yield 'NT official sample B' => [NtDriverLicenceNumber::class, '1164381', '1164381'];
        yield 'Qld official sample as printed' => [QldDriverLicenceNumber::class, '123 456 789', '123456789'];
        yield 'Qld eight digits' => [QldDriverLicenceNumber::class, '12345678', '12345678'];
        yield 'SA six alphanumerics' => [SaDriverLicenceNumber::class, 'S12345', 'S12345'];
        yield 'SA six digits (the sample prints 000000)' => [SaDriverLicenceNumber::class, '000000', '000000'];
        yield 'Tas seven alphanumerics' => [TasDriverLicenceNumber::class, 'A123456', 'A123456'];
        yield 'Tas eight alphanumerics' => [TasDriverLicenceNumber::class, 'AB123456', 'AB123456'];
        yield 'Vic official sample' => [VicDriverLicenceNumber::class, '123456789', '123456789'];
        yield 'Vic ten digits' => [VicDriverLicenceNumber::class, '0123456789', '0123456789'];
        yield 'WA official sample' => [WaDriverLicenceNumber::class, '6437772', '6437772'];
        yield 'separators, tabs and NBSP' => [WaDriverLicenceNumber::class, " 643-7\t77\u{00A0}2 ", '6437772'];
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
        yield 'ACT' => [ActDriverLicenceNumber::class, Jurisdiction::Act, '1234567890'];
        yield 'NSW' => [NswDriverLicenceNumber::class, Jurisdiction::Nsw, '01234567'];
        yield 'NT' => [NtDriverLicenceNumber::class, Jurisdiction::Nt, '1564102'];
        yield 'Qld' => [QldDriverLicenceNumber::class, Jurisdiction::Qld, '123456789'];
        yield 'SA' => [SaDriverLicenceNumber::class, Jurisdiction::Sa, 'S12345'];
        yield 'Tas' => [TasDriverLicenceNumber::class, Jurisdiction::Tas, 'A123456'];
        yield 'Vic' => [VicDriverLicenceNumber::class, Jurisdiction::Vic, '123456789'];
        yield 'WA' => [WaDriverLicenceNumber::class, Jurisdiction::Wa, '6437772'];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('jurisdictions')]
    public function testTellsItsJurisdiction(string $class, Jurisdiction $jurisdiction, string $sample): void
    {
        self::assertSame($jurisdiction, $class::from($sample)->jurisdiction());
    }

    public function testDifferentJurisdictionsAreNotEqual(): void
    {
        self::assertFalse(VicDriverLicenceNumber::from('1234567')->equals(WaDriverLicenceNumber::from('1234567')));
        self::assertTrue(WaDriverLicenceNumber::from('1234567')->equals(WaDriverLicenceNumber::from('123 4567')));
    }

    /**
     * @return iterable<string, array{
     *     class-string<AbstractDriverLicenceNumber>,
     *     int<1, max>,
     *     int<1, max>,
     *     non-empty-string,
     * }>
     */
    public static function lengthRanges(): iterable
    {
        yield 'ACT' => [ActDriverLicenceNumber::class, 1, 10, 'AU-ACT'];
        yield 'NSW' => [NswDriverLicenceNumber::class, 6, 8, 'AU-NSW'];
        yield 'NT' => [NtDriverLicenceNumber::class, 1, 10, 'AU-NT'];
        yield 'Qld' => [QldDriverLicenceNumber::class, 8, 9, 'AU-QLD'];
        yield 'SA' => [SaDriverLicenceNumber::class, 6, 6, 'AU-SA'];
        yield 'Tas' => [TasDriverLicenceNumber::class, 6, 8, 'AU-TAS'];
        yield 'Vic' => [VicDriverLicenceNumber::class, 1, 10, 'AU-VIC'];
        yield 'WA' => [WaDriverLicenceNumber::class, 7, 7, 'AU-WA'];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     * @param int<1, max> $min
     * @param int<1, max> $max
     * @param non-empty-string $iso
     */
    #[DataProvider('lengthRanges')]
    public function testAcceptsExactlyThePublishedLengthRange(string $class, int $min, int $max, string $iso): void
    {
        self::assertSame(\str_repeat('1', $min), $class::from(\str_repeat('1', $min))->value);
        self::assertSame(\str_repeat('1', $max), $class::from(\str_repeat('1', $max))->value);
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractDriverLicenceNumber => $class::from($value),
            \str_repeat('1', $min - 1),
            \sprintf('Invalid %s driver licence number (%d characters).', $iso, $min - 1),
        );
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractDriverLicenceNumber => $class::from($value),
            \str_repeat('1', $max + 1),
            \sprintf('Invalid %s driver licence number (%d characters).', $iso, $max + 1),
        );
    }

    /**
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function maskedForms(): iterable
    {
        yield 'ten characters keep three' => [ActDriverLicenceNumber::class, '1234567890', '*******890'];
        yield 'eight characters keep three' => [NswDriverLicenceNumber::class, '01234567', '*****567'];
        yield 'letters are masked too' => [TasDriverLicenceNumber::class, 'AB123456', '*****456'];
        yield 'six characters keep three' => [SaDriverLicenceNumber::class, 'S12345', '***345'];
        yield 'five characters keep two' => [VicDriverLicenceNumber::class, '12345', '***45'];
        yield 'two characters keep one' => [NtDriverLicenceNumber::class, '12', '*2'];
        yield 'one character keeps none' => [VicDriverLicenceNumber::class, '7', '*'];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('maskedForms')]
    public function testMasksAllButTheLastCharacters(string $class, string $input, string $expected): void
    {
        self::assertSame($expected, $class::from($input)->masked());
    }

    /**
     * @return iterable<string, array{class-string<AbstractDriverLicenceNumber>, string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'ACT eleven digits' => [
            ActDriverLicenceNumber::class,
            '12345678901',
            'Invalid AU-ACT driver licence number (11 characters).',
        ];
        yield 'ACT letter' => [
            ActDriverLicenceNumber::class,
            '123456789A',
            'Invalid AU-ACT driver licence number (invalid character).',
        ];
        yield 'ACT empty' => [
            ActDriverLicenceNumber::class,
            '',
            'Invalid AU-ACT driver licence number (0 characters).',
        ];
        yield 'NSW five characters' => [
            NswDriverLicenceNumber::class,
            '12345',
            'Invalid AU-NSW driver licence number (5 characters).',
        ];
        yield 'NSW nine characters' => [
            NswDriverLicenceNumber::class,
            '123456789',
            'Invalid AU-NSW driver licence number (9 characters).',
        ];
        yield 'NSW non-ASCII letter' => [
            NswDriverLicenceNumber::class,
            '1234567É',
            'Invalid AU-NSW driver licence number (invalid character).',
        ];
        yield 'NT letter' => [
            NtDriverLicenceNumber::class,
            '156410Z',
            'Invalid AU-NT driver licence number (invalid character).',
        ];
        yield 'Qld seven digits' => [
            QldDriverLicenceNumber::class,
            '1234567',
            'Invalid AU-QLD driver licence number (7 characters).',
        ];
        yield 'Qld ten digits' => [
            QldDriverLicenceNumber::class,
            '1234567890',
            'Invalid AU-QLD driver licence number (10 characters).',
        ];
        yield 'SA five characters' => [
            SaDriverLicenceNumber::class,
            'S1234',
            'Invalid AU-SA driver licence number (5 characters).',
        ];
        yield 'SA seven characters' => [
            SaDriverLicenceNumber::class,
            'S123456',
            'Invalid AU-SA driver licence number (7 characters).',
        ];
        yield 'Tas nine characters' => [
            TasDriverLicenceNumber::class,
            'A12345678',
            'Invalid AU-TAS driver licence number (9 characters).',
        ];
        yield 'Tas other separator' => [
            TasDriverLicenceNumber::class,
            'A12.345',
            'Invalid AU-TAS driver licence number (invalid character).',
        ];
        yield 'Vic letter' => [
            VicDriverLicenceNumber::class,
            '12345678O',
            'Invalid AU-VIC driver licence number (invalid character).',
        ];
        yield 'WA six digits' => [
            WaDriverLicenceNumber::class,
            '643777',
            'Invalid AU-WA driver licence number (6 characters).',
        ];
        yield 'WA eight digits' => [
            WaDriverLicenceNumber::class,
            '64377721',
            'Invalid AU-WA driver licence number (8 characters).',
        ];
        yield 'WA trailing control character' => [
            WaDriverLicenceNumber::class,
            "643777\x00",
            'Invalid AU-WA driver licence number (invalid character).',
        ];
        yield 'pasted several times' => [
            WaDriverLicenceNumber::class,
            \str_repeat('6437772 ', 6),
            'Invalid AU-WA driver licence number (too long).',
        ];
    }

    /**
     * @param class-string<AbstractDriverLicenceNumber> $class
     */
    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $class, string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): AbstractDriverLicenceNumber => $class::from($value),
            $input,
            $message,
        );
    }
}
