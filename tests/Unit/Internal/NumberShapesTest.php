<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberShapes::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
final class NumberShapesTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validInputs(): iterable
    {
        yield 'first shape, upper-cased' => ['a12', 'A12'];
        yield 'second shape of the same length' => ['123', '123'];
        yield 'shape of another length' => ['12345', '12345'];
        yield 'separators removed' => [" 1-2\u{00A0}3\t4 5\n", '12345'];
    }

    #[DataProvider('validInputs')]
    public function testAcceptsEveryShape(string $input, string $expected): void
    {
        self::assertSame($expected, $this->shapes()->parse($input, 'X'));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidInputs(): iterable
    {
        yield 'length between the shapes' => ['1234', 'Invalid X (4 characters).'];
        yield 'below every shape' => ['12', 'Invalid X (2 characters).'];
        yield 'above every shape' => ['123456', 'Invalid X (6 characters).'];
        yield 'empty' => ['', 'Invalid X (0 characters).'];
        yield 'far too long' => [\str_repeat('1', 41), 'Invalid X (too long).'];
        yield 'shapes are not mixed across lengths' => ['A1234', 'Invalid X (invalid character).'];
        yield 'no shape of that length matches' => ['AB1', 'Invalid X (invalid character).'];
        yield 'a shape is anchored at the end' => ["123\x00", 'Invalid X (4 characters).'];
        yield 'multibyte letter counted once' => ['12É', 'Invalid X (invalid character).'];
        yield 'alternation is anchored as a whole' => ['1234A', 'Invalid X (invalid character).'];
    }

    #[DataProvider('invalidInputs')]
    public function testRefusesWithLengthOrInvalidCharacter(string $input, string $message): void
    {
        $this->expectException(InvalidDocumentNumberException::class);
        $this->expectExceptionMessage($message);

        $this->shapes()->parse($input, 'X');
    }

    public function testFitsLengthCountsCharactersAfterTheSeparatorsWithoutCheckingThem(): void
    {
        self::assertTrue($this->shapes()->fitsLength('A-B C'));
        self::assertTrue($this->shapes()->fitsLength('É12'));
        self::assertFalse($this->shapes()->fitsLength('1234'));
        self::assertFalse($this->shapes()->fitsLength(''));
    }

    private function shapes(): NumberShapes
    {
        return new NumberShapes(['[A-Z][0-9]{2}' => 3, '[0-9]{3}' => 3, '[0-9]{5}' => 5]);
    }
}
