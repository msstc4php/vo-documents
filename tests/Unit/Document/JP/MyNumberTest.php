<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\JP;

use MSSTC4PHP\ValueObject\Documents\Document\JP\MyNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/JP.md "My Number". The ministerial ordinance and J-LIS publish the formula but no
 * example number; the numbers below are computed by hand from the quoted formula (the remainder in the comment).
 */
#[CoversClass(MyNumber::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class MyNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'remainder 3, check 8' => ['123456789018', '123456789018'];
        yield 'remainder 0 gives check 0' => ['000000000140', '000000000140'];
        yield 'remainder 1 gives check 0' => ['123456789000', '123456789000'];
        yield 'remainder 10 gives check 1' => ['000000000051', '000000000051'];
        yield 'remainder 2 gives check 9' => ['000000000019', '000000000019'];
        yield 'groups of four' => ['1234 5678 9018', '123456789018'];
        yield 'hyphens and NBSP' => ["1234-5678\u{00A0}9018", '123456789018'];
        yield 'ideographic space' => ["1234\u{3000}5678\u{3000}9018", '123456789018'];
        yield 'fullwidth digits with fullwidth hyphens' => [
            "\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF0D}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF0D}"
            . "\u{FF19}\u{FF10}\u{FF11}\u{FF18}",
            '123456789018',
        ];
        yield 'fullwidth digits' => [
            "\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF19}\u{FF10}\u{FF11}\u{FF18}",
            '123456789018',
        ];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = MyNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('********9018', MyNumber::from('123456789018')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'check digit changed' => ['123456789019', 'Invalid My Number checksum.'];
        yield 'number digit changed' => ['123456788018', 'Invalid My Number checksum.'];
        yield 'check 0 where the remainder gives 8' => ['123456789010', 'Invalid My Number checksum.'];
        yield 'check 1 where the remainder is 1' => ['123456789001', 'Invalid My Number checksum.'];
        yield 'too short' => ['12345678901', 'Invalid My Number (wrong length).'];
        yield 'too long' => ['1234567890180', 'Invalid My Number (wrong length).'];
        yield 'letter' => ['12345678901O', 'Invalid My Number (invalid character).'];
        yield 'empty' => ['', 'Invalid My Number (wrong length).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): MyNumber => MyNumber::from($value),
            $input,
            $message,
        );
    }
}
