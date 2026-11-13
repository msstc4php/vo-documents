<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\CH;

use MSSTC4PHP\ValueObject\Documents\Document\CH\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/CH.md "Driving licence number". `123456789004` is the ASTRA specimen of the 2023
 * licence, `000123456789003` the specimen of the 2003 licence.
 */
#[CoversClass(DriverLicenceNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class DriverLicenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'ASTRA specimen 2023' => ['123456789004', '123456789004'];
        yield 'ASTRA specimen 2003' => ['000123456789003', '000123456789003'];
        yield 'spaces and NBSP' => ["123 456\u{00A0}789 004", '123456789004'];
        yield 'hyphens' => ['123456789-004', '123456789004'];
        yield 'grouped 2003 layout' => ['0001-2345-6789-003', '000123456789003'];
        yield 'grouped 2023 layout with spaces' => ['123 456 789 004 ', '123456789004'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = DriverLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
    }

    public function testSplitsTheTwelveDigitNumber(): void
    {
        $number = DriverLicenceNumber::from('123456789004');

        self::assertSame('123456789', $number->faberPin());
        self::assertSame('004', $number->sequenceNumber());
    }

    public function testTheFifteenDigitLayoutIsNotSplit(): void
    {
        $number = DriverLicenceNumber::from('000123456789003');

        self::assertNull($number->faberPin());
        self::assertNull($number->sequenceNumber());
    }

    public function testMasksAllButTheLastFourDigits(): void
    {
        self::assertSame('********9004', DriverLicenceNumber::from('123456789004')->masked());
        self::assertSame('***********9003', DriverLicenceNumber::from('000123456789003')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $invalid = 'Invalid Swiss driving licence number (invalid character).';

        yield 'eleven digits' => ['12345678900', 'Invalid Swiss driving licence number (11 characters).'];
        yield 'thirteen digits' => ['1234567890041', 'Invalid Swiss driving licence number (13 characters).'];
        yield 'fourteen digits' => ['00123456789003', 'Invalid Swiss driving licence number (14 characters).'];
        yield 'sixteen digits' => ['0000123456789003', 'Invalid Swiss driving licence number (16 characters).'];
        yield 'letter' => ['12345678900A', $invalid];
        yield 'letter in the 2003 layout' => ['00012345678900A', $invalid];
        yield 'multibyte letter' => ["12345678900\u{00E9}", $invalid];
        yield 'dot' => ['123.456789004', 'Invalid Swiss driving licence number (13 characters).'];
        yield 'empty' => ['', 'Invalid Swiss driving licence number (0 characters).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid Swiss driving licence number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): DriverLicenceNumber => DriverLicenceNumber::from($value),
            $input,
            $message,
        );
    }
}
