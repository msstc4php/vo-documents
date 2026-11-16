<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Document\MX\ElectorKey;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/MX.md "Clave de elector" (INE lineamientos 2021, 1.III.b; INE glossary). INE
 * publishes no example key; the keys below are made up to the published layout.
 */
#[CoversClass(ElectorKey::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
#[UsesClass(TwoDigitYearDate::class)]
final class ElectorKeyTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validKeys(): iterable
    {
        yield 'plain' => ['GMLPJN56123109H400', 'GMLPJN56123109H400'];
        yield 'lower case and spaces' => ['gmlpjn 561231 09 h 400', 'GMLPJN56123109H400'];
        yield '29 February of a year divisible by four' => ['GMLPJN00022914X401', 'GMLPJN00022914X401'];
        yield 'letters in state and homoclave' => ['GMLPJN561231ABMA0B', 'GMLPJN561231ABMA0B'];
    }

    #[DataProvider('validKeys')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $key = ElectorKey::from($input);

        self::assertSame($expected, $key->value);
        self::assertSame($expected, (string) $key);
    }

    public function testReadsTheFieldsAsWritten(): void
    {
        $key = ElectorKey::from('GMLPJN56123109H400');

        self::assertSame('56', $key->birthYearDigits());
        self::assertSame(12, $key->birthMonth());
        self::assertSame(31, $key->birthDay());
        self::assertSame('09', $key->birthStateCode());
        self::assertSame('H', $key->sexCode());

        $other = ElectorKey::from('GMLPJN00022914X401');
        self::assertSame('00', $other->birthYearDigits());
        self::assertSame(2, $other->birthMonth());
        self::assertSame(29, $other->birthDay());
        self::assertSame('14', $other->birthStateCode());
        self::assertSame('X', $other->sexCode());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('**************H400', ElectorKey::from('GMLPJN56123109H400')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidKeys(): iterable
    {
        yield 'too short' => ['GMLPJN56123109H40', 'Invalid Mexican elector key (17 characters).'];
        yield 'too long' => ['GMLPJN56123109H4001', 'Invalid Mexican elector key (19 characters).'];
        yield 'punctuation' => ['GMLPJN56123109H40.', 'Invalid Mexican elector key (invalid character).'];
        yield 'digit among the letters' => ['GMLPJ156123109H400', 'Invalid Mexican elector key (invalid character).'];
        yield 'letter in the date' => ['GMLPJN5612A109H400', 'Invalid Mexican elector key (invalid character).'];
        yield 'digit as sex' => ['GMLPJN561231091400', 'Invalid Mexican elector key (invalid character).'];
        yield '30 February' => ['GMLPJN56023009H400', 'Invalid Mexican elector key (invalid birth date).'];
        yield '29 February 02' => ['GMLPJN02022909H400', 'Invalid Mexican elector key (invalid birth date).'];
        yield '29 February 01' => ['GMLPJN01022909H400', 'Invalid Mexican elector key (invalid birth date).'];
        yield 'month 13' => ['GMLPJN56130109H400', 'Invalid Mexican elector key (invalid birth date).'];
        yield 'day 00' => ['GMLPJN56120009H400', 'Invalid Mexican elector key (invalid birth date).'];
    }

    #[DataProvider('invalidKeys')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): ElectorKey => ElectorKey::from($value),
            $input,
            $message,
        );
    }
}
