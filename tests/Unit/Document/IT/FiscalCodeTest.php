<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\IT;

use MSSTC4PHP\ValueObject\Documents\Document\IT\FiscalCode;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IT.md "Codice fiscale" (DM 23 dicembre 1976). The decree has no example number;
 * the check letters below were computed with its art. 7 tables.
 */
#[CoversClass(FiscalCode::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(TwoDigitYearDate::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class FiscalCodeTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validCodes(): iterable
    {
        yield 'man born in Rome' => ['RSSMRA80A01H501U', 'RSSMRA80A01H501U'];
        yield 'lower case, spaces, NBSP' => ["rssmra 80a01\u{00A0}h501u", 'RSSMRA80A01H501U'];
        yield 'hyphens' => ['RSSMRA-80A01-H501U', 'RSSMRA80A01H501U'];
        yield 'woman (day plus 40)' => ['BNCLRA85M71F205F', 'BNCLRA85M71F205F'];
        yield 'foreign state of birth' => ['RSSMRA80A01Z404R', 'RSSMRA80A01Z404R'];
    }

    #[DataProvider('validCodes')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $code = FiscalCode::from($input);

        self::assertSame($expected, $code->value);
        self::assertSame($expected, (string) $code);
        self::assertSame($expected, $code->jsonSerialize());
    }

    public function testReadsTheFieldsOfAMan(): void
    {
        $code = FiscalCode::from('VRDGPP13R10B293P');

        self::assertSame(Sex::Male, $code->sex());
        self::assertSame('13', $code->birthYearDigits());
        self::assertSame(10, $code->birthMonth());
        self::assertSame(10, $code->birthDay());
        self::assertSame('B293', $code->birthPlaceCode());
    }

    public function testReadsTheFieldsOfAWoman(): void
    {
        $code = FiscalCode::from('BNCLRA85M71F205F');

        self::assertSame(Sex::Female, $code->sex());
        self::assertSame('85', $code->birthYearDigits());
        self::assertSame(8, $code->birthMonth());
        self::assertSame(31, $code->birthDay());
        self::assertSame('F205', $code->birthPlaceCode());
    }

    public function testFirstAndLastMonthLetters(): void
    {
        self::assertSame(1, FiscalCode::from('RSSMRA80A01H501U')->birthMonth());
        self::assertSame(12, FiscalCode::from(self::withCheck('RSSMRA80T31H501'))->birthMonth());
        self::assertSame(31, FiscalCode::from(self::withCheck('RSSMRA80T31H501'))->birthDay());
        self::assertSame(1, FiscalCode::from(self::withCheck('RSSMRA80A41H501'))->birthDay());
        self::assertSame(Sex::Female, FiscalCode::from(self::withCheck('RSSMRA80A41H501'))->sex());
        self::assertSame(Sex::Male, FiscalCode::from(self::withCheck('RSSMRA80A31H501'))->sex());
    }

    /**
     * @return iterable<string, array{string, string, string, int, string}>
     */
    public static function omocodes(): iterable
    {
        yield 'last digit replaced (1 = M)' => ['RSSMRA80A01H50MM', '80', 'H501', 1, Sex::Male->value];
        yield 'two digits replaced (0 = L)' => ['RSSMRA80A01H5LMX', '80', 'H501', 1, Sex::Male->value];
        yield 'all seven digits replaced' => ['RSSMRAULALMHRLMD', '80', 'H501', 1, Sex::Male->value];
        yield 'day digits replaced, woman' => [self::withCheck('BNCLRA85MTMF205'), '85', 'F205', 31, 'female'];
        yield 'letters for 8, 9, 2, 3, 4, 5, 6' => [self::withCheck('RSSMRAUVANPHQRS'), '89', 'H456', 23, 'male'];
        yield 'letters for 6, 7, 1, 0' => [self::withCheck('RSSMRASTAMTHLLL'), '67', 'H000', 17, 'male'];
    }

    #[DataProvider('omocodes')]
    public function testReadsTheFieldsOfAnOmocode(
        string $code,
        string $year,
        string $place,
        int $day,
        string $sex,
    ): void {
        $fiscalCode = FiscalCode::from($code);

        self::assertSame($code, $fiscalCode->value);
        self::assertSame($year, $fiscalCode->birthYearDigits());
        self::assertSame($place, $fiscalCode->birthPlaceCode());
        self::assertSame($day, $fiscalCode->birthDay());
        self::assertSame($sex, $fiscalCode->sex()->value);
    }

    public function testLeapDays(): void
    {
        self::assertSame(29, FiscalCode::from('RSSMRA04B29H501C')->birthDay());
        self::assertSame(29, FiscalCode::from('RSSMRA00B29H501Y')->birthDay(), '2000 was a leap year');
        self::assertSame(28, FiscalCode::from('RSSMRA01B28H501X')->birthDay());
        self::assertSame(29, FiscalCode::from(self::withCheck('RSSMRA12B29H501'))->birthDay());
        self::assertSame(29, FiscalCode::from(self::withCheck('RSSMRA96B29H501'))->birthDay());
        self::assertSame(2, FiscalCode::from(self::withCheck('RSSMRAULB69H501'))->birthMonth());
    }

    public function testMasks(): void
    {
        self::assertSame('************501U', FiscalCode::from('RSSMRA80A01H501U')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidCodes(): iterable
    {
        $invalid = 'Invalid Italian fiscal code (invalid character).';
        $day = 'Invalid Italian fiscal code (invalid birth day).';

        yield 'fifteen characters' => ['RSSMRA80A01H501', 'Invalid Italian fiscal code (15 characters).'];
        yield 'seventeen characters' => ['RSSMRA80A01H501UX', 'Invalid Italian fiscal code (17 characters).'];
        yield 'eleven-digit provisional number' => ['12345678903', 'Invalid Italian fiscal code (11 characters).'];
        yield 'empty' => ['', 'Invalid Italian fiscal code (0 characters).'];
        yield 'digit in the surname' => [self::withCheck('R1SMRA80A01H501'), $invalid];
        yield 'digit in the name' => [self::withCheck('RSSMR180A01H501'), $invalid];
        yield 'letter outside the omocodia table in the year' => [self::withCheck('RSSMRA8AA01H501'), $invalid];
        yield 'O is not the letter for 4' => [self::withCheck('RSSMRA80A01H50O'), $invalid];
        yield 'letter outside the table in the day' => [self::withCheck('RSSMRA80A0KH501'), $invalid];
        yield 'letter outside the table in the place' => [self::withCheck('RSSMRA80A01HA01'), $invalid];
        yield 'digit as the month' => [self::withCheck('RSSMRA80101H501'), $invalid];
        yield 'digit as the place letter' => [self::withCheck('RSSMRA80A011501'), $invalid];
        yield 'digit as the check character' => ['RSSMRA80A01H5011', $invalid];
        yield 'accented letter' => ['RSSMRÀ80A01H501U', $invalid];
        yield 'month letter outside the table' => [
            self::withCheck('RSSMRA80F01H501'),
            'Invalid Italian fiscal code (invalid birth month).',
        ];
        yield 'day 00' => [self::withCheck('RSSMRA80A00H501'), $day];
        yield 'day 32' => [self::withCheck('RSSMRA80A32H501'), $day];
        yield 'day 40' => [self::withCheck('RSSMRA80A40H501'), $day];
        yield 'day 72' => [self::withCheck('RSSMRA80A72H501'), $day];
        yield 'day 81' => [self::withCheck('RSSMRA80A81H501'), $day];
        yield 'day 91, omocode' => [self::withCheck('RSSMRA80AVMH501'), $day];
        yield '31 April' => [self::withCheck('RSSMRA80D31H501'), $day];
        yield '30 February' => [self::withCheck('RSSMRA80B30H501'), $day];
        yield '29 February of a year not divisible by four' => [self::withCheck('RSSMRA01B29H501'), $day];
        yield '29 February 10' => [self::withCheck('RSSMRA10B29H501'), $day];
        yield '29 February 98' => [self::withCheck('RSSMRA98B29H501'), $day];
        yield '29 February plus 40, omocode' => [self::withCheck('RSSMRALMBSVH501'), $day];
        yield '31 November, woman' => [self::withCheck('RSSMRA80S71H501'), $day];
        yield 'wrong check letter' => ['RSSMRA80A01H501V', 'Invalid Italian fiscal code checksum.'];
        yield 'omocode with the check letter of the plain code' => [
            'RSSMRA80A01H50MU',
            'Invalid Italian fiscal code checksum.',
        ];
        yield 'two characters swapped' => ['SRSMRA80A01H501U', 'Invalid Italian fiscal code checksum.'];
    }

    #[DataProvider('invalidCodes')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): FiscalCode => FiscalCode::from($value),
            $input,
            $message,
        );
    }

    /**
     * Appends the art. 7 check letter, computed here independently of the class.
     */
    private static function withCheck(string $first): string
    {
        $odd = [1, 0, 5, 7, 9, 13, 15, 17, 19, 21, 2, 4, 18, 20, 11, 3, 6, 8, 12, 14, 16, 10, 22, 25, 24, 23];
        $sum = 0;
        foreach (\str_split(\substr($first, 0, 15)) as $index => $character) {
            $rank = \ctype_digit($character) ? (int) $character : \ord($character) - 65;
            $rank = \max(0, \min(25, $rank));
            $sum += $index % 2 === 0 ? $odd[$rank] : $rank;
        }

        return $first . \chr(65 + $sum % 26);
    }
}
