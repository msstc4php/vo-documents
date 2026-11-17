<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\IN;

use MSSTC4PHP\ValueObject\Documents\Document\IN\PassportNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/IN.md "Passport number": the MEA data-page field `CHAR(8)`, specimens `J1234002`
 * and `B5007771`.
 */
#[CoversClass(PassportNumber::class)]
#[UsesClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PassportNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'MEA specimen' => ['J1234002', 'J1234002'];
        yield 'MEA old-passport specimen' => ['B5007771', 'B5007771'];
        yield 'lower case, separators, NBSP' => ["j 1234-002\u{00A0}", 'J1234002'];
        yield 'fullwidth' => ["\u{FF2A}\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF10}\u{FF10}\u{FF12}", 'J1234002'];
        yield 'any 8 letters or digits (format only)' => ['AB123456', 'AB123456'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = PassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastThree(): void
    {
        self::assertSame('*****002', PassportNumber::from('J1232002')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $name = 'Invalid Indian passport number';

        yield 'seven characters' => ['J123400', $name . ' (7 characters).'];
        yield 'nine characters' => ['J12340021', $name . ' (9 characters).'];
        yield 'far too long' => [\str_repeat('J', 41), $name . ' (too long).'];
        yield 'punctuation' => ['J123400.', $name . ' (invalid character).'];
        yield 'Latin-1 letter' => ["J123400\u{00C9}", $name . ' (invalid character).'];
        yield 'Devanagari digit' => ["J123400\u{0968}", $name . ' (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): PassportNumber => PassportNumber::from($value),
            $input,
            $message,
        );
    }
}
