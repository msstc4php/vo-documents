<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "Driving licence". The source has no check digit and no example number, so the
 * value is checked by its shape only.
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
        yield 'formatted' => ['99 99 123456', '9999123456'];
        yield 'digits only' => ['9999123456', '9999123456'];
        yield 'series and number' => ['9999 123456', '9999123456'];
        yield 'tab and NBSP' => ["99\t99\u{00A0}123456", '9999123456'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = DriverLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $number = DriverLicenceNumber::from('1234567890');

        self::assertSame('12 34 567890', $number->formatted());
        self::assertSame('** ** **7890', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'too short' => ['99 99 12345', 'Invalid Russian driving licence number (9 characters).'];
        yield 'too long' => ['99 99 1234567', 'Invalid Russian driving licence number (11 characters).'];
        yield '1999 form with Cyrillic series' => ['77 АВ 123456', 'Invalid Russian driving licence number (invalid character).'];
        yield 'Latin letters' => ['77 AB 123456', 'Invalid Russian driving licence number (invalid character).'];
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
