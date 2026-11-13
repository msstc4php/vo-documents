<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Document\FR\Nir;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/FR.md "NIR" (INSEE example `1 21 12 77 131 232 - 50`; DSN field rules; the key
 * is not verified).
 */
#[CoversClass(Nir::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class NirTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'INSEE example, display form' => ['1 21 12 77 131 232 - 50', '121127713123250'];
        yield 'compact' => ['121127713123250', '121127713123250'];
        yield 'NBSP and narrow NBSP' => ["1\u{00A0}21 12 77 131\u{202F}232 50", '121127713123250'];
        yield 'any key accepted (not verified)' => ['121127713123299', '121127713123299'];
        yield 'key 00 accepted' => ['121127713123200', '121127713123200'];
        yield 'Corsica 2A' => ['2850 52A004 012 33', '285052A00401233'];
        yield 'Corsica 2b, lower case' => ['2850 52b030 001 07', '285052B03000107'];
        yield 'born abroad (99), month 20' => ['185209910012345', '185209910012345'];
        yield 'month 30' => ['185307513100145', '185307513100145'];
        yield 'month 42' => ['185427513100145', '185427513100145'];
        yield 'month 50' => ['185507513100145', '185507513100145'];
        yield 'month 99, department 01, commune and order 999' => ['185990199999945', '185990199999945'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheCompactForm(string $input, string $expected): void
    {
        $number = Nir::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testExposesTheFieldsTheSourcesDefine(): void
    {
        $number = Nir::from('121127713123250');

        self::assertSame(Sex::Male, $number->sex());
        self::assertSame('21', $number->birthYearDigits());
        self::assertSame(12, $number->birthMonth());
        self::assertSame('77131', $number->birthPlaceCode());
        self::assertSame('232', $number->orderNumber());
        self::assertSame('50', $number->controlKey());
    }

    public function testSexTwoIsFemaleAndCorsicaIsKeptInThePlaceCode(): void
    {
        $number = Nir::from('285012A00401233');

        self::assertSame(Sex::Female, $number->sex());
        self::assertSame(1, $number->birthMonth());
        self::assertSame('2A004', $number->birthPlaceCode());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unknownMonths(): iterable
    {
        yield '20' => ['185207513100145'];
        yield '30' => ['185307513100145'];
        yield '42' => ['185427513100145'];
        yield '50' => ['185507513100145'];
        yield '99' => ['185997513100145'];
    }

    #[DataProvider('unknownMonths')]
    public function testAMonthAboveTwelveIsUnknown(string $input): void
    {
        self::assertNull(Nir::from($input)->birthMonth());
    }

    public function testFormatsAndMasks(): void
    {
        $number = Nir::from('121127713123250');

        self::assertSame('1 21 12 77 131 232 - 50', $number->formatted());
        self::assertSame('* ** ** ** *** *32 - 50', $number->masked());
        self::assertSame('2 85 05 2A 004 012 - 33', Nir::from('285052A00401233')->formatted());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $document = 'Invalid French NIR';

        yield 'thirteen digits, no key' => ['1211277131232', $document . ' (13 characters).'];
        yield 'fourteen digits' => ['12112771312325', $document . ' (14 characters).'];
        yield 'sixteen characters' => ['1211277131232500', $document . ' (16 characters).'];
        yield 'empty' => ['', $document . ' (0 characters).'];
        yield 'letter C' => ['12112771312C250', $document . ' (invalid character).'];
        yield 'A outside the department' => ['A21127713123250', $document . ' (invalid character).'];
        yield 'B in the key' => ['1211277131232B0', $document . ' (invalid character).'];
        yield 'A in the commune' => ['12112772A123250', $document . ' (invalid character).'];
        yield 'department 2C' => ['121122C13123250', $document . ' (invalid character).'];
        yield 'department A2' => ['12112A213123250', $document . ' (invalid character).'];
        yield 'sex digit 3' => ['321127713123250', $document . ' (invalid sex digit).'];
        yield 'temporary number 7' => ['721127713123250', $document . ' (invalid sex digit).'];
        yield 'sex digit 0' => ['021127713123250', $document . ' (invalid sex digit).'];
        yield 'month 00' => ['121007713123250', $document . ' (invalid birth month).'];
        yield 'month 13' => ['121137713123250', $document . ' (invalid birth month).'];
        yield 'month 19' => ['121197713123250', $document . ' (invalid birth month).'];
        yield 'month 21' => ['121217713123250', $document . ' (invalid birth month).'];
        yield 'month 29' => ['121297713123250', $document . ' (invalid birth month).'];
        yield 'month 43' => ['121437713123250', $document . ' (invalid birth month).'];
        yield 'month 49' => ['121497713123250', $document . ' (invalid birth month).'];
        yield 'department 00' => ['121120013123250', $document . ' (invalid department).'];
        yield 'commune 000' => ['121127700023250', $document . ' (invalid commune).'];
        yield 'order number 000' => ['121127713100050', $document . ' (invalid order number).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Nir => Nir::from($value), $input, $message);
    }
}
