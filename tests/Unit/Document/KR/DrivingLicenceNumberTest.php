<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\KR;

use MSSTC4PHP\ValueObject\Documents\Document\KR\DrivingLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/KR.md "Driving licence number": KoROAD's notice of 2014-07-02, `11-95-255933-61`.
 */
#[CoversClass(DrivingLicenceNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class DrivingLicenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'compact' => ['119525593361', '119525593361'];
        yield 'KoROAD display form' => ['11-95-255933-61', '119525593361'];
        yield 'spaces and NBSP' => ["11 95\u{00A0}255933 61", '119525593361'];
        yield 'fullwidth' => ["\u{FF11}\u{FF11}\u{FF19}\u{FF15}\u{FF12}\u{FF15}\u{FF15}\u{FF19}\u{FF13}\u{FF13}\u{FF16}\u{FF11}", '119525593361'];
        yield 'a code the notice does not list' => ['289525593361', '289525593361'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = DrivingLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testRegionCodeAndTheDisplayForm(): void
    {
        $number = DrivingLicenceNumber::from('119525593361');

        self::assertSame('11', $number->regionCode());
        self::assertSame('11-95-255933-61', $number->formatted());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('********3361', DrivingLicenceNumber::from('11-95-255933-61')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid Korean driving licence number';

        yield 'empty' => ['', $name . ' (0 characters).'];
        yield 'ten digits, the old form without a region number' => ['9525593361', $name . ' (10 characters).'];
        yield 'eleven digits' => ['11952559336', $name . ' (11 characters).'];
        yield 'thirteen digits' => ['1195255933611', $name . ' (13 characters).'];
        yield 'far too long' => [\str_repeat('1', 41), $name . ' (too long).'];
        yield 'a letter' => ['11952559336A', $name . ' (invalid character).'];
        yield 'region name in Hangul' => ['서울9525593361', $name . ' (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): DrivingLicenceNumber => DrivingLicenceNumber::from($value), $input, $message);
    }
}
