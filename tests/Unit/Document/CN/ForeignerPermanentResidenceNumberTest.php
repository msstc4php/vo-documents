<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CN;

use DateTimeZone;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Document\CN\ForeignerPermanentResidenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Gb11643;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\TamperedValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CN.md "Foreigner's permanent residence ID card" (NIA, 2023-10, annex 2, table 1).
 */
#[CoversClass(ForeignerPermanentResidenceNumber::class)]
#[UsesClass(Gb11643::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
#[UsesClass(Checksum::class)]
final class ForeignerPermanentResidenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;
    use TamperedValue;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'NIA example' => ['932682198501010017', '932682198501010017'];
        yield 'separators' => ['9 32682-19850101 0017', '932682198501010017'];
        yield 'fullwidth digits' => [
            "\u{FF19}\u{FF13}\u{FF12}\u{FF16}\u{FF18}\u{FF12}\u{FF11}\u{FF19}\u{FF18}\u{FF15}\u{FF10}\u{FF11}" .
                "\u{FF10}\u{FF11}\u{FF10}\u{FF10}\u{FF11}\u{FF17}",
            '932682198501010017',
        ];
        yield '29 February 1980' => ['932682198002290018', '932682198002290018'];
        yield 'check X' => ['900000198501010012', '900000198501010012'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = ForeignerPermanentResidenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testReadsTheFieldsOfTheNiaExample(): void
    {
        $number = ForeignerPermanentResidenceNumber::from('932682198501010017');

        self::assertSame('32', $number->applicationPlaceCode());
        self::assertSame('682', $number->nationalityCode());
        self::assertSame('1985-01-01 00:00:00', $number->birthDate()->format('Y-m-d H:i:s'));
        self::assertEquals(new DateTimeZone('UTC'), $number->birthDate()->getTimezone());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('**************0017', ForeignerPermanentResidenceNumber::from('932682198501010017')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $document = 'Invalid Chinese permanent residence card number';

        yield 'old 15-character form' => ['KAZ110090123105', $document . ' (15 characters).'];
        yield 'too short' => ['93268219850101001', $document . ' (17 characters).'];
        yield 'too long' => ['9326821985010100170', $document . ' (19 characters).'];
        yield 'first digit 8 with a valid check' => ['822682198501010011', $document . ' (invalid character).'];
        yield 'first digit 1 with a valid check' => ['11010519491231002X', $document . ' (invalid character).'];
        yield 'letter inside' => ['93268219850101A017', $document . ' (invalid character).'];
        yield 'checksum off' => ['932682198501010021', $document . ' checksum.'];
        yield 'check 7 replaced by 8' => ['932682198501010018', $document . ' checksum.'];
        yield '29 February 1985' => ['932682198502290014', $document . ' (invalid birth date).'];
        yield 'month 13' => ['932682198513010014', $document . ' (invalid birth date).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): ForeignerPermanentResidenceNumber => ForeignerPermanentResidenceNumber::from(
                $value,
            ),
            $input,
            $message,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function tamperedValues(): iterable
    {
        yield 'month 13' => ['110105199013071239'];
        yield 'day 00' => ['110105199003001239'];
        yield 'letter in the year that casts to a valid year' => ['110105199a03071239'];
        yield 'too short' => ['110105'];
    }

    #[DataProvider('tamperedValues')]
    public function testATamperedBirthDateFailsWithFixedText(string $value): void
    {
        $number = self::tampered(ForeignerPermanentResidenceNumber::class, $value);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('GB 11643 number validated by assertBirthDate().');

        $number->birthDate();
    }
}
