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
 * Expectations follow docs/sources/KR.md "Driving licence number": KoROAD's notice of 2014-07-02, `11-95-255933-61`
 * and its region-name table.
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
        yield 'fullwidth' => [
            "\u{FF11}\u{FF11}\u{FF19}\u{FF15}\u{FF12}\u{FF15}\u{FF15}\u{FF19}\u{FF13}\u{FF13}\u{FF16}\u{FF11}",
            '119525593361',
        ];
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

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function regions(): iterable
    {
        foreach (
            [
                '서울' => '11', '부산' => '12', '경기' => '13', '강원' => '14', '충북' => '15', '충남' => '16',
                '전북' => '17', '전남' => '18', '경북' => '19', '경남' => '20', '제주' => '21', '대구' => '22',
                '인천' => '23', '광주' => '24', '대전' => '25', '울산' => '26',
            ] as $name => $code
        ) {
            yield $name => [$name, $code];
        }
    }

    #[DataProvider('regions')]
    public function testEarlierFormMapsTheRegionNameThroughKoroadsTable(string $name, string $code): void
    {
        $number = DrivingLicenceNumber::from($name . ' 95-255933-61');

        self::assertSame($name . ' 95-255933-61', $number->value);
        self::assertSame($name . ' 95-255933-61', $number->formatted());
        self::assertSame($code, $number->regionCode());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function earlierForms(): iterable
    {
        yield 'KoROAD print' => ['서울 95-255933-61'];
        yield 'compact' => ['서울9525593361'];
        yield 'other separators' => ["서울\u{00A0}95 255933-61"];
        yield 'fullwidth digits' => ["서울 \u{FF19}\u{FF15}-255933-61"];
        yield 'decomposed Hangul' => ["\u{1109}\u{1165}\u{110B}\u{116E}\u{11AF} 95-255933-61"];
    }

    #[DataProvider('earlierForms')]
    public function testEarlierFormIsKeptAsKoroadPrintsIt(string $input): void
    {
        $number = DrivingLicenceNumber::from($input);

        self::assertSame('서울 95-255933-61', $number->value);
        self::assertSame('서울 95-255933-61', (string) $number);
        self::assertSame('서울 95-255933-61', $number->jsonSerialize());
        self::assertFalse($number->equals(DrivingLicenceNumber::from('119525593361')));
    }

    public function testEarlierFormMasksAllButTheLastFourDigits(): void
    {
        self::assertSame('서울 **-****33-61', DrivingLicenceNumber::from('서울 95-255933-61')->masked());
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
        yield 'ten digits, no region' => ['9525593361', $name . ' (10 characters).'];
        yield 'eleven digits' => ['11952559336', $name . ' (11 characters).'];
        yield 'thirteen digits' => ['1195255933611', $name . ' (13 characters).'];
        yield 'far too long' => [\str_repeat('1', 41), $name . ' (too long).'];
        yield 'a letter' => ['11952559336A', $name . ' (invalid character).'];
        yield 'unknown region name' => ['세종9525593361', $name . ' (invalid character).'];
        yield 'region name and nine digits' => ['서울952559336', $name . ' (11 characters).'];
        yield 'region name and eleven digits' => ['서울95255933611', $name . ' (13 characters).'];
        yield 'region name only' => ['서울', $name . ' (2 characters).'];
        yield 'region name in Latin' => ['SE9525593361', $name . ' (invalid character).'];
        yield 'three-letter region name' => ['서울시9525593361', $name . ' (13 characters).'];
        yield 'a character before the region name' => ['가서울9525593361', $name . ' (13 characters).'];
        yield 'region name after the digits' => ['9525593361서울', $name . ' (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): DrivingLicenceNumber => DrivingLicenceNumber::from($value),
            $input,
            $message,
        );
    }
}
