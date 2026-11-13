<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use InvalidArgumentException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
final class NumberFormatTest extends TestCase
{
    public function testStripsSeparatorsAndUpperCases(): void
    {
        self::assertSame('AB12', new NumberFormat('0-9A-Z', 4, 4)->parse(" a-b\u{00A0}12 ", 'X'));
    }

    public function testAcceptsBothLengthBounds(): void
    {
        $format = new NumberFormat('0-9', 2, 3);

        self::assertSame('12', $format->parse('12', 'X'));
        self::assertSame('123', $format->parse('123', 'X'));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidInputs(): iterable
    {
        yield 'below the minimum' => ['1', 'Invalid X (1 characters).'];
        yield 'above the maximum' => ['1234', 'Invalid X (4 characters).'];
        yield 'empty' => ['', 'Invalid X (0 characters).'];
        yield 'far too long' => [\str_repeat('1', 41), 'Invalid X (too long).'];
        yield 'letter at the right length' => ['12A', 'Invalid X (invalid character).'];
        yield 'letter first' => ['A12', 'Invalid X (invalid character).'];
        yield 'multibyte letter counted once' => ['12É', 'Invalid X (invalid character).'];
        yield 'multibyte letter beyond the length' => ['123É', 'Invalid X (4 characters).'];
        yield 'trailing line feed is a separator, control character is not' => ["12\x01", 'Invalid X (invalid character).'];
    }

    #[DataProvider('invalidInputs')]
    public function testRefusesWithLengthOrInvalidCharacter(string $input, string $message): void
    {
        $this->expectException(InvalidDocumentNumberException::class);
        $this->expectExceptionMessage($message);

        new NumberFormat('0-9', 2, 3)->parse($input, 'X');
    }

    public function testRefusesAnInvertedLengthRange(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new NumberFormat('0-9', 3, 2);
    }

    public function testAcceptsAOneLengthRange(): void
    {
        self::assertSame('7', new NumberFormat('0-9', 1, 1)->parse('7', 'X'));
    }
}
