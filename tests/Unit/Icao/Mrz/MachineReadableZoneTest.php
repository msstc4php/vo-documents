<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Icao\Mrz;

use DateTimeImmutable;
use DateTimeZone;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidMrzException;
use MSSTC4PHP\ValueObject\Documents\Icao\Mrz\MachineReadableZone;
use MSSTC4PHP\ValueObject\Documents\Icao\Mrz\Td1;
use MSSTC4PHP\ValueObject\Documents\Icao\Mrz\Td2;
use MSSTC4PHP\ValueObject\Documents\Icao\Mrz\Td3;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzCheckDigit;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzDocumentNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzField;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzLayout;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\I18n\CountryCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(MachineReadableZone::class)]
#[CoversClass(Td1::class)]
#[CoversClass(Td2::class)]
#[CoversClass(Td3::class)]
#[UsesClass(PersonName::class)]
#[UsesClass(UnicodeText::class)]
#[UsesClass(MrzCheckDigit::class)]
#[UsesClass(MrzDocumentNumber::class)]
#[UsesClass(MrzField::class)]
#[UsesClass(MrzLayout::class)]
#[UsesClass(TwoDigitYearDate::class)]
#[UsesClass(Text::class)]
final class MachineReadableZoneTest extends TestCase
{
    private const string TD3_LINE_1 = 'PPUTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<';

    private const string TD3_LINE_2 = 'L898902C36UTO7408122F3404159ZE184226B<<<<<16';

    private const string TD2 = "I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\nD231458907UTO7408122F1204159<<<<<<<6";

    private const string TD1 = "I<UTOD231458907<<<<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<6\nERIKSSON<<ANNA<MARIA<<<<<<<<<<";

    private const string TD1_LONG = "I<UTOABC123456<783<<<<<<<<<<<<\n7408122F1204159UTO<<<<<<<<<<<0\nERIKSSON<<ANNA<MARIA<<<<<<<<<<";

    private const string TD3 = self::TD3_LINE_1 . "\n" . self::TD3_LINE_2;

    public function testTd3SpecimenOfPart4ParsesFieldByField(): void
    {
        $mrz = MachineReadableZone::fromString(self::TD3);

        self::assertInstanceOf(Td3::class, $mrz);
        self::assertSame('PP', $mrz->documentCode());
        self::assertSame('UTO', $mrz->issuingState());
        self::assertSame('UTO', $mrz->nationality());
        self::assertSame('L898902C3', $mrz->documentNumber());
        self::assertSame('ZE184226B', $mrz->optionalData());
        self::assertSame('740812', $mrz->birthDateDigits());
        self::assertSame('340415', $mrz->expiryDateDigits());
        self::assertSame(Sex::Female, $mrz->sex());
        self::assertNull($mrz->issuingCountry());
        self::assertNull($mrz->nationalityCountry());
        self::assertSame(self::TD3, (string) $mrz);
        self::assertSame(self::TD3, $mrz->jsonSerialize());
    }

    public function testTheNameOfTheTd3SpecimenIsTheVisualZoneName(): void
    {
        $name = MachineReadableZone::fromString(self::TD3)->name();

        self::assertTrue($name->equals(PersonName::fromParts('ERIKSSON', ['ANNA', 'MARIA'])));
        self::assertSame('ERIKSSON, ANNA MARIA', $name->visualZone());
    }

    public function testTd2SpecimenOfPart6ParsesFieldByField(): void
    {
        $mrz = MachineReadableZone::fromString(self::TD2);

        self::assertInstanceOf(Td2::class, $mrz);
        self::assertSame('I', $mrz->documentCode());
        self::assertSame('UTO', $mrz->issuingState());
        self::assertSame('UTO', $mrz->nationality());
        self::assertSame('D23145890', $mrz->documentNumber());
        self::assertSame('', $mrz->optionalData());
        self::assertSame('740812', $mrz->birthDateDigits());
        self::assertSame('120415', $mrz->expiryDateDigits());
        self::assertSame(Sex::Female, $mrz->sex());
        self::assertSame('ERIKSSON, ANNA MARIA', $mrz->name()->visualZone());
        self::assertSame(self::TD2, (string) $mrz);
    }

    public function testTd1SpecimenOfPart5ParsesFieldByField(): void
    {
        $mrz = MachineReadableZone::fromString(self::TD1);

        self::assertInstanceOf(Td1::class, $mrz);
        self::assertSame('I', $mrz->documentCode());
        self::assertSame('UTO', $mrz->issuingState());
        self::assertSame('UTO', $mrz->nationality());
        self::assertSame('D23145890', $mrz->documentNumber());
        self::assertSame('', $mrz->optionalData());
        self::assertSame('', $mrz->secondOptionalData());
        self::assertSame('740812', $mrz->birthDateDigits());
        self::assertSame('120415', $mrz->expiryDateDigits());
        self::assertSame(Sex::Female, $mrz->sex());
        self::assertSame('ERIKSSON, ANNA MARIA', $mrz->name()->visualZone());
        self::assertSame(self::TD1, (string) $mrz);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function specimens(): iterable
    {
        yield 'TD3' => [self::TD3];
        yield 'TD2' => [self::TD2];
        yield 'TD1' => [self::TD1];
        yield 'TD1 long number' => [self::TD1_LONG];
    }

    #[DataProvider('specimens')]
    public function testRoundTripsByteExact(string $text): void
    {
        $mrz = MachineReadableZone::fromString($text);

        self::assertSame($text, (string) $mrz);
        self::assertTrue($mrz->equals(MachineReadableZone::fromString((string) $mrz)));
        self::assertSame($text, MachineReadableZone::from($text)->value);
    }

    #[DataProvider('specimens')]
    public function testCrlfGivesTheCanonicalForm(string $text): void
    {
        $crlf = \str_replace("\n", "\r\n", $text);

        self::assertSame($text, (string) MachineReadableZone::fromString($crlf));
    }

    #[DataProvider('specimens')]
    public function testASingleStringOfTheFormatLengthGivesTheCanonicalForm(string $text): void
    {
        self::assertSame($text, (string) MachineReadableZone::fromString(\str_replace("\n", '', $text)));
    }

    public function testTheConcreteClassesRefuseOtherFormats(): void
    {
        self::assertSame(self::TD3, Td3::from(self::TD3)->value);
        self::assertNull(Td3::tryFrom(self::TD2));
        self::assertNull(Td2::tryFrom(self::TD1));
        self::assertNull(Td1::tryFrom(self::TD3));
        self::assertTrue(MachineReadableZone::isValid(self::TD1));
        self::assertFalse(Td1::isValid(self::TD3));

        $this->expectException(InvalidMrzException::class);
        Td1::from(self::TD2);
    }

    public function testTheCompositeCheckDigitIsEnforced(): void
    {
        foreach ([self::TD3, self::TD2, self::TD1] as $text) {
            $wrong = \substr($text, 0, -1) . (str_ends_with($text, '6') ? '7' : '6');
            if (\str_starts_with($text, 'I<UTOD231458907<<<')) {
                $wrong = \str_replace('<<<<<<<<<<<6', '<<<<<<<<<<<7', $text);
            }

            $this->assertRefusedAs('Invalid ICAO MRZ check digit.', $wrong);
        }
    }

    public function testEveryOtherCheckDigitIsEnforced(): void
    {
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('L898902C36', 'L898902C37', self::TD3));
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('7408122F', '7408123F', self::TD3));
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('F3404159', 'F3404158', self::TD3));
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('ZE184226B<<<<<1', 'ZE184226B<<<<<2', self::TD3));
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('D231458907', 'D231458908', self::TD2));
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('D231458907', 'D231458908', self::TD1));
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('7408122F', '7408123F', self::TD1));
    }

    public function testABlankPersonalNumberMayHaveAFillerOrAZeroAsItsCheckDigit(): void
    {
        $filler = self::TD3_LINE_1 . "\nL898902C36UTO7408122F3404159" . \str_repeat('<', 15) . '4';
        $zero = self::TD3_LINE_1 . "\nL898902C36UTO7408122F3404159" . \str_repeat('<', 14) . '04';

        self::assertSame('', MachineReadableZone::fromString($filler)->optionalData());
        self::assertSame('', MachineReadableZone::fromString($zero)->optionalData());
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', self::TD3_LINE_1 . "\nL898902C36UTO7408122F3404159ZE184226B<<<<<<6");
    }

    public function testATd1LongDocumentNumberJoinsTheOptionalDataOverflow(): void
    {
        $mrz = MachineReadableZone::fromString(self::TD1_LONG);

        self::assertSame('ABC12345678', $mrz->documentNumber());
        self::assertSame('', $mrz->optionalData());
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', \str_replace('<783<', '<784<', self::TD1_LONG));
    }

    public function testATd2LongDocumentNumber(): void
    {
        $check = MrzCheckDigit::of('ABC12345612345');
        $line2 = 'ABC123456<UTO7408122F120415912345' . $check . '<';
        $composite = MrzCheckDigit::of(\substr($line2, 0, 10) . \substr($line2, 13, 7) . \substr($line2, 21, 14));
        $mrz = Td2::from("I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\n" . $line2 . $composite);

        self::assertSame('ABC12345612345', $mrz->documentNumber());
        self::assertSame('', $mrz->optionalData());
    }

    public function testABirthDateWithAnUnknownDayIsNull(): void
    {
        $mrz = MachineReadableZone::fromString(
            self::TD3_LINE_1 . "\nL898902C36UTO7408<<7F3404159ZE184226B<<<<<16",
        );

        self::assertSame('7408<<', $mrz->birthDateDigits());
        self::assertNull($mrz->birthDate(new DateTimeImmutable('2026-11-21T00:00:00Z')));
    }

    public function testAnUnknownBirthDateIsNull(): void
    {
        $mrz = MachineReadableZone::fromString(
            self::TD3_LINE_1 . "\nL898902C36UTO<<<<<<0F3404159ZE184226B<<<<<16",
        );

        self::assertSame('<<<<<<', $mrz->birthDateDigits());
        self::assertNull($mrz->birthDate(new DateTimeImmutable('2026-11-21T00:00:00Z')));
    }

    public function testAPartlyUnknownBirthDateNeedsWholePairs(): void
    {
        $this->assertRefusedAs('Invalid ICAO MRZ field.', self::TD3_LINE_1 . "\nL898902C36UTO7408<2" . '7F3404159ZE184226B<<<<<16');
    }

    public function testTheBirthDateTakesTheMostRecentPastCentury(): void
    {
        $mrz = MachineReadableZone::fromString(self::TD3);
        $today = new DateTimeImmutable('2026-11-21T10:00:00Z');

        self::assertSame('1974-08-12', $mrz->birthDate($today)?->format('Y-m-d'));
        self::assertSame('2074-08-12', $mrz->birthDate(new DateTimeImmutable('2074-08-12T00:00:00Z'))?->format('Y-m-d'));
        self::assertSame('1974-08-12', $mrz->birthDate(new DateTimeImmutable('2074-08-11T23:59:59Z'))?->format('Y-m-d'));
        self::assertSame('2074-08-12', $mrz->birthDate(new DateTimeImmutable('2074-08-11T23:59:59-05:00'))?->format('Y-m-d'));
        self::assertSame('1974-08-12', $mrz->birthDate(new DateTimeImmutable('2074-08-11T18:00:00-05:00'))?->format('Y-m-d'));
        $birth = $mrz->birthDate($today);
        self::assertSame('UTC', $birth->getTimezone()->getName());
    }

    public function testABirthOnA29thOfFebruaryOfACenturyYearFallsBackToALeapCentury(): void
    {
        $line2 = 'L898902C36UTO000229?F3404159ZE184226B<<<<<1';
        $birth = '000229';
        $line2 = 'L898902C36UTO' . $birth . MrzCheckDigit::of($birth) . 'F3404159ZE184226B<<<<<1';
        $composite = MrzCheckDigit::of(\substr($line2, 0, 10) . \substr($line2, 13, 7) . \substr($line2, 21, 22));
        $mrz = Td3::from(self::TD3_LINE_1 . "\n" . $line2 . $composite);

        self::assertSame('2000-02-29', $mrz->birthDate(new DateTimeImmutable('2026-11-21T00:00:00Z'))?->format('Y-m-d'));
        self::assertSame('1600-02-29', $mrz->birthDate(new DateTimeImmutable('1999-11-21T00:00:00Z'))?->format('Y-m-d'));
    }

    public function testTheExpiryDateTakesTheCenturyNearestToTheReference(): void
    {
        $mrz = MachineReadableZone::fromString(self::TD3);

        self::assertSame('2034-04-15', $mrz->expiryDate(new DateTimeImmutable('2026-11-21T00:00:00Z'))->format('Y-m-d'));
        self::assertSame('2034-04-15', $mrz->expiryDate(new DateTimeImmutable('1990-01-01T00:00:00Z'))->format('Y-m-d'));
        self::assertSame('1934-04-15', $mrz->expiryDate(new DateTimeImmutable('1980-01-01T00:00:00Z'))->format('Y-m-d'));
        self::assertSame('2134-04-15', $mrz->expiryDate(new DateTimeImmutable('2090-01-01T00:00:00Z'))->format('Y-m-d'));
        self::assertSame('2034-04-15', $mrz->expiryDate(new DateTimeImmutable('2034-04-15T12:00:00+14:00'))->format('Y-m-d'));
        self::assertSame('UTC', $mrz->expiryDate(new DateTimeImmutable('2026-11-21T00:00:00Z'))->getTimezone()->getName());
    }

    public function testAnImpossibleDateIsRefused(): void
    {
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withBirthDate('741312'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withBirthDate('740832'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withBirthDate('740230'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withBirthDate('74<<00'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withBirthDate('740012'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withBirthDate('74<<32'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withExpiryDate('34<<15'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withExpiryDate('340230'));
    }

    public function testAnUnknownMonthOrYearWithAKnownDayParses(): void
    {
        self::assertNull(MachineReadableZone::fromString($this->withBirthDate('74<<12'))->birthDate(new DateTimeImmutable('2026-01-01T00:00:00Z')));
        self::assertNull(MachineReadableZone::fromString($this->withBirthDate('<<0812'))->birthDate(new DateTimeImmutable('2026-01-01T00:00:00Z')));
        self::assertSame('<<0229', MachineReadableZone::fromString($this->withBirthDate('<<0229'))->birthDateDigits());
    }

    public function testSexIsNullForAFiller(): void
    {
        $male = MachineReadableZone::fromString($this->withSex('M'));
        $unspecified = MachineReadableZone::fromString($this->withSex('<'));

        self::assertSame(Sex::Male, $male->sex());
        self::assertNull($unspecified->sex());
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withSex('X'));
    }

    public function testGermanyIsIssuedAsDWithTwoFillers(): void
    {
        $mrz = Td3::from($this->withCodes('PP', 'D<<', 'D<<'));

        self::assertSame('D', $mrz->issuingState());
        self::assertSame('D', $mrz->nationality());
        self::assertSame(CountryCode::DE, $mrz->issuingCountry());
        self::assertSame('DEU', $mrz->issuingCountry()->alpha3());
        self::assertSame(CountryCode::DE, $mrz->nationalityCountry());
    }

    public function testCodesOutsideIso3166AreKeptRawAndHaveNoCountry(): void
    {
        foreach (['GBD', 'GBP', 'UNO', 'UNA', 'XXA', 'XXB', 'XXC', 'XXX', 'RKS', 'EUE', 'XOM'] as $code) {
            $mrz = Td3::from($this->withCodes('PP', $code, $code));

            self::assertSame($code, $mrz->issuingState());
            self::assertSame($code, $mrz->nationality());
            self::assertNull($mrz->issuingCountry(), $code);
            self::assertNull($mrz->nationalityCountry(), $code);
        }
    }

    public function testIso3166CodesGiveACountry(): void
    {
        $mrz = Td3::from($this->withCodes('PP', 'FRA', 'GBR'));

        self::assertSame(CountryCode::FR, $mrz->issuingCountry());
        self::assertSame(CountryCode::GB, $mrz->nationalityCountry());
    }

    public function testABlankNationalityIsNull(): void
    {
        $mrz = Td3::from($this->withCodes('PP', 'UTO', '<<<'));

        self::assertNull($mrz->nationality());
        self::assertNull($mrz->nationalityCountry());
    }

    public function testTheDocumentCodeRules(): void
    {
        self::assertSame('PE', Td3::from($this->withCodes('PE', 'UTO', 'UTO'))->documentCode());
        self::assertSame('P', Td3::from($this->withCodes('P<', 'UTO', 'UTO'))->documentCode());
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withCodes('IP', 'UTO', 'UTO'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withCodes('P1', 'UTO', 'UTO'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withCodes('PP', '<<<', 'UTO'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withCodes('PP', 'U<T', 'UTO'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withCodes('PP', 'UT1', 'UTO'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withCodes('PP', 'UTO', 'U<T'));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', \str_replace('I<UTO', 'PPUTO', self::TD2));
        $this->assertRefusedAs('Invalid ICAO MRZ field.', \str_replace('I<UTO', 'V<UTO', self::TD1));
        self::assertSame('AC', Td2::from($this->withTd2DocumentCode('AC'))->documentCode());
        self::assertSame('C', Td2::from($this->withTd2DocumentCode('C<'))->documentCode());
    }

    public function testTheNameMustBeAMrzName(): void
    {
        $this->assertRefusedAs('Invalid ICAO MRZ name.', $this->withTd3Name('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<'));
        $this->assertRefusedAs('Invalid ICAO MRZ name.', $this->withTd3Name('ERIKSSON1<ANNA<<<<<<<<<<<<<<<<<<<<<<<<<<'));
        $this->assertRefusedAs('Invalid ICAO MRZ name.', $this->withTd3Name('<<ANNA<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<'));
        $this->assertRefusedAs('Invalid ICAO MRZ name.', $this->withTd3Name('ERIKSSON<<<ANNA<<<<<<<<<<<<<<<<<<<<<<<<<<<'));
    }

    public function testMaskedHidesTheDocumentNumberTheOptionalDataAndTheBirthDate(): void
    {
        self::assertSame(
            self::TD3_LINE_1 . "\n" . \str_repeat('*', 10) . 'UTO' . \str_repeat('*', 7) . 'F3404159' . \str_repeat('*', 16),
            MachineReadableZone::fromString(self::TD3)->masked(),
        );
        self::assertSame(
            "I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\n" . \str_repeat('*', 10) . 'UTO' . \str_repeat('*', 7) . 'F1204159' . \str_repeat('*', 8),
            MachineReadableZone::fromString(self::TD2)->masked(),
        );
        self::assertSame(
            'I<UTO' . \str_repeat('*', 25) . "\n" . \str_repeat('*', 7) . 'F1204159UTO' . \str_repeat('*', 12) . "\nERIKSSON<<ANNA<MARIA<<<<<<<<<<",
            MachineReadableZone::fromString(self::TD1)->masked(),
        );
    }

    #[DataProvider('specimens')]
    public function testAMaskedMrzKeepsItsShapeAndNeverParses(string $text): void
    {
        $mrz = MachineReadableZone::fromString($text);
        $masked = $mrz->masked();

        self::assertSame(\strlen($text), \strlen($masked));
        self::assertSame(\substr_count($text, "\n"), \substr_count($masked, "\n"));
        self::assertStringNotContainsString($mrz->documentNumber(), $masked);
        self::assertStringNotContainsString($mrz->birthDateDigits(), $masked);
        self::assertStringContainsString($mrz->expiryDateDigits(), $masked);
        self::assertFalse(MachineReadableZone::isValid($masked));
        self::assertNull(MachineReadableZone::tryFrom($masked));
    }

    public function testMaskedHidesTheTd1LongNumberOverflow(): void
    {
        self::assertStringNotContainsString('123456', MachineReadableZone::fromString(self::TD1_LONG)->masked());
        self::assertStringNotContainsString('78', \explode("\n", MachineReadableZone::fromString(self::TD1_LONG)->masked())[0]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function malformed(): iterable
    {
        yield 'empty' => [''];
        yield 'one line' => [self::TD3_LINE_1];
        yield 'td3 with a third line' => [self::TD3 . "\n" . self::TD3_LINE_2];
        yield 'td3 line too short' => [self::TD3_LINE_1 . "\n" . \substr(self::TD3_LINE_2, 1)];
        yield 'td3 line too long' => [self::TD3_LINE_1 . "\n" . self::TD3_LINE_2 . '<'];
        yield 'trailing newline' => [self::TD3 . "\n"];
        yield 'lone carriage return' => [\str_replace("\n", "\r", self::TD3)];
        yield 'lowercase' => [\strtolower(self::TD3)];
        yield 'space' => [\str_replace('ANNA<MARIA', 'ANNA MARIA', self::TD3)];
        yield 'asterisk' => [\str_replace('L898902C3', 'L898902C*', self::TD3)];
        yield 'single string of 87' => [\substr(\str_replace("\n", '', self::TD3), 1)];
        yield 'single string of 89' => [\str_replace("\n", '', self::TD3) . '<'];
        yield 'td1 and td2 lines mixed' => [self::TD2 . "\n" . 'ERIKSSON<<ANNA<MARIA<<<<<<<<<<'];
    }

    #[DataProvider('malformed')]
    public function testRefusesMalformedInput(string $input): void
    {
        $this->assertRefusedAs('Invalid ICAO MRZ.', $input);
        self::assertNull(MachineReadableZone::tryFrom($input));
    }

    public function testNoMessageHoldsAnyPartOfTheInput(): void
    {
        $inputs = [
            \str_replace('F3404159', 'F3404158', self::TD3),
            \str_replace('I<UTO', 'V<UTO', self::TD1),
            $this->withBirthDate('741312'),
            $this->withTd3Name('ERIKSSON1<ANNA<<<<<<<<<<<<<<<<<<<<<<<<<<'),
            self::TD3 . "\n" . 'SECRETSECRET',
        ];

        foreach ($inputs as $input) {
            try {
                MachineReadableZone::fromString($input);
                self::fail('Expected a refusal.');
            } catch (InvalidMrzException $exception) {
                $message = $exception->getMessage();
                self::assertMatchesRegularExpression('/^Invalid ICAO MRZ[a-z ]*\.$/', $message);
                self::assertDoesNotMatchRegularExpression('/[0-9]/', $message);
                \preg_match_all('/[A-Z0-9<]{3,}/', \str_replace("\n", '', $input), $runs);
                foreach ($runs[0] as $run) {
                    for ($i = 0; $i + 3 <= \strlen($run); $i++) {
                        self::assertStringNotContainsString(\substr($run, $i, 3), $message, $message);
                    }
                }
            }
        }
    }

    public function testEqualityIsByCanonicalValue(): void
    {
        self::assertTrue(MachineReadableZone::fromString(self::TD2)->equals(MachineReadableZone::fromString(\str_replace("\n", "\r\n", self::TD2))));
        self::assertFalse(MachineReadableZone::fromString(self::TD2)->equals(MachineReadableZone::fromString(self::TD1)));
    }

    public function testTheMrzIsNotAPublicAbstractConstruction(): void
    {
        self::assertTrue(new ReflectionClass(MachineReadableZone::class)->isAbstract());
        self::assertTrue(new ReflectionClass(Td1::class)->isFinal());
        self::assertTrue(new ReflectionClass(Td2::class)->isFinal());
        self::assertTrue(new ReflectionClass(Td3::class)->isFinal());
        self::assertSame('UTC', new DateTimeZone('UTC')->getName());
    }

    public function testEveryFieldOfAFullTd3IsReadFromItsOwnPositions(): void
    {
        $mrz = Td3::from("PAFRAPAPANDROPOULOUS<<JONATHON<WARREN<TREVOR\nAB12345671GBR6503123M3001019Z1Y2X3W4V5U6T700");

        self::assertSame('PA', $mrz->documentCode());
        self::assertSame('FRA', $mrz->issuingState());
        self::assertSame('GBR', $mrz->nationality());
        self::assertSame('AB1234567', $mrz->documentNumber());
        self::assertSame('Z1Y2X3W4V5U6T7', $mrz->optionalData());
        self::assertSame('650312', $mrz->birthDateDigits());
        self::assertSame('300101', $mrz->expiryDateDigits());
        self::assertSame(Sex::Male, $mrz->sex());
        self::assertTrue($mrz->name()->equals(PersonName::fromParts('PAPANDROPOULOUS', ['JONATHON', 'WARREN', 'TREVOR'])));
    }

    public function testEveryFieldOfAFullTd2IsReadFromItsOwnPositions(): void
    {
        $mrz = Td2::from("IDFRAPAPANDROPOULOUS<<JONATHOON<ALEC\nXY98765435DEU9909092F3112319ABCDEFG5");

        self::assertSame('ID', $mrz->documentCode());
        self::assertSame('FRA', $mrz->issuingState());
        self::assertSame('DEU', $mrz->nationality());
        self::assertSame('XY9876543', $mrz->documentNumber());
        self::assertSame('ABCDEFG', $mrz->optionalData());
        self::assertSame('990909', $mrz->birthDateDigits());
        self::assertSame('311231', $mrz->expiryDateDigits());
        self::assertSame(Sex::Female, $mrz->sex());
        self::assertTrue($mrz->name()->equals(PersonName::fromParts('PAPANDROPOULOUS', ['JONATHOON', 'ALEC'])));
    }

    public function testEveryFieldOfAFullTd1IsReadFromItsOwnPositions(): void
    {
        $mrz = Td1::from("IDFRAKL76543219OPT1OPT2OPT3OPT\n8802299M2911308ITAABCDEFGHIJK1\nNILAVADHANANANDA<<CHAYAPA<DE<K");

        self::assertSame('ID', $mrz->documentCode());
        self::assertSame('FRA', $mrz->issuingState());
        self::assertSame('ITA', $mrz->nationality());
        self::assertSame('KL7654321', $mrz->documentNumber());
        self::assertSame('OPT1OPT2OPT3OPT', $mrz->optionalData());
        self::assertSame('ABCDEFGHIJK', $mrz->secondOptionalData());
        self::assertSame('880229', $mrz->birthDateDigits());
        self::assertSame('291130', $mrz->expiryDateDigits());
        self::assertSame(Sex::Male, $mrz->sex());
        self::assertSame('NILAVADHANANANDA', $mrz->name()->primaryIdentifier());
        self::assertSame(['CHAYAPA', 'DE', 'K'], $mrz->name()->secondaryIdentifiers());
    }

    public function testEachIndividualCheckDigitIsEnforcedWhenTheCompositeIsRight(): void
    {
        $line1 = "PPUTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\n";

        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', $line1 . 'L898902C36UTO7408123F3404159ZE184226B<<<<<19');
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', $line1 . 'L898902C36UTO7408122F3404158ZE184226B<<<<<15');
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', $line1 . 'L898902C37UTO7408122F3404159ZE184226B<<<<<13');
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', $line1 . 'L898902C36UTO7408122F3404159ZE184226B<<<<<27');
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', $line1 . 'L898902C36UTO7408122F3404159ZE184226B<<<<<<5');
        $this->assertRefusedAs('Invalid ICAO MRZ check digit.', $line1 . 'L898902C36UTO7408122F3404159<<<<<<<<<<<<<<59');
    }

    public function testCalendarBoundariesOfTheDates(): void
    {
        foreach (['740101', '741231', '720229', '<<0229', '7412<<', '74<<31', '7401<<', '740131'] as $birth) {
            self::assertSame($birth, MachineReadableZone::fromString($this->withBirthDate($birth))->birthDateDigits());
        }

        foreach (['740229', '7413<<', '74<<32', '740431', '740100', '740001'] as $birth) {
            $this->assertRefusedAs('Invalid ICAO MRZ field.', $this->withBirthDate($birth));
        }

        self::assertSame('340101', MachineReadableZone::fromString($this->withExpiryDate('340101'))->expiryDateDigits());
        self::assertSame('341231', MachineReadableZone::fromString($this->withExpiryDate('341231'))->expiryDateDigits());
    }

    public function testTheBirthCenturyFollowsTheReferenceCentury(): void
    {
        $mrz = MachineReadableZone::fromString($this->withBirthDate('000101'));

        self::assertSame('2000-01-01 00:00:00', $mrz->birthDate(new DateTimeImmutable('2000-06-01T00:00:00Z'))?->format('Y-m-d H:i:s'));
        self::assertSame('1900-01-01 00:00:00', $mrz->birthDate(new DateTimeImmutable('1999-06-01T00:00:00Z'))?->format('Y-m-d H:i:s'));
    }

    public function testTheExpiryCenturyMayBeThePreviousOne(): void
    {
        $mrz = MachineReadableZone::fromString($this->withExpiryDate('950415'));

        self::assertSame('1995-04-15', $mrz->expiryDate(new DateTimeImmutable('2000-01-01T00:00:00Z'))->format('Y-m-d'));
        self::assertSame('2095-04-15', $mrz->expiryDate(new DateTimeImmutable('2060-01-01T00:00:00Z'))->format('Y-m-d'));
        self::assertSame('2095-04-15 00:00:00', $mrz->expiryDate(new DateTimeImmutable('2095-04-15T00:00:00Z'))->format('Y-m-d H:i:s'));
    }

    public function testTheExpiryOfA29thOfFebruarySkipsCenturiesWithoutIt(): void
    {
        $mrz = MachineReadableZone::fromString($this->withExpiryDate('000229'));

        self::assertSame('2000-02-29', $mrz->expiryDate(new DateTimeImmutable('1950-01-01T00:00:00Z'))->format('Y-m-d'));
    }

    private function assertRefusedAs(string $message, string $input): void
    {
        try {
            MachineReadableZone::fromString($input);
        } catch (InvalidMrzException $exception) {
            self::assertSame($message, $exception->getMessage());

            return;
        }

        self::fail('Expected an InvalidMrzException.');
    }

    private function withBirthDate(string $digits): string
    {
        return $this->td3(birth: $digits);
    }

    private function withExpiryDate(string $digits): string
    {
        return $this->td3(expiry: $digits);
    }

    private function withSex(string $sex): string
    {
        return $this->td3(sex: $sex);
    }

    private function withCodes(string $documentCode, string $issuer, string $nationality): string
    {
        return $this->td3(code: $documentCode, issuer: $issuer, nationality: $nationality);
    }

    private function withTd2DocumentCode(string $code): string
    {
        $line1 = $code . 'UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<';

        return $line1 . "\n" . 'D231458907UTO7408122F1204159<<<<<<<6';
    }

    private function withTd3Name(string $name): string
    {
        return $this->td3(name: \substr(\str_pad($name, 39, '<'), 0, 39));
    }

    /**
     * A TD3 whose check digits are recomputed, so only the field under test can make it invalid.
     */
    private function td3(
        string $code = 'PP',
        string $issuer = 'UTO',
        string $name = 'ERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<',
        string $nationality = 'UTO',
        string $birth = '740812',
        string $sex = 'F',
        string $expiry = '340415',
    ): string {
        $number = 'L898902C3';
        $personal = 'ZE184226B<<<<<';
        $birthCheck = \preg_match('/^[0-9<]{6}$/', $birth) === 1 ? (string) MrzCheckDigit::of($birth) : '0';
        $expiryCheck = \preg_match('/^[0-9<]{6}$/', $expiry) === 1 ? (string) MrzCheckDigit::of($expiry) : '0';
        $line2 = $number . MrzCheckDigit::of($number) . $nationality . $birth . $birthCheck . $sex . $expiry
            . $expiryCheck . $personal . MrzCheckDigit::of($personal);
        $composite = MrzCheckDigit::of(\substr($line2, 0, 10) . \substr($line2, 13, 7) . \substr($line2, 21, 22));

        return $code . $issuer . $name . "\n" . $line2 . $composite;
    }
}
