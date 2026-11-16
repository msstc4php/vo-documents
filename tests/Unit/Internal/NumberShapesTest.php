<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use InvalidArgumentException;
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

    /**
     * @return iterable<string, array{non-empty-string, int}>
     */
    public static function derivedLengths(): iterable
    {
        yield 'one class' => ['[0-9]', 1];
        yield 'one letter' => ['H', 1];
        yield 'repeated class' => ['[0-9]{9}', 9];
        yield 'two-digit count' => ['[0-9]{12}', 12];
        yield 'letters and classes' => ['WDL[0-9A-Z]{9}', 12];
        yield 'classes with an asterisk' => ['[A-Z*]{7}[0-9]{2}[0-9*][0-9A-Z]{2}', 12];
    }

    /**
     * @param non-empty-string $shape
     */
    #[DataProvider('derivedLengths')]
    public function testComputesTheLengthFromTheShape(string $shape, int $length): void
    {
        $shapes = new NumberShapes($shape);

        self::assertTrue($shapes->fitsLength(\str_repeat('x', $length)));
        self::assertFalse($shapes->fitsLength(\str_repeat('x', $length - 1)));
        self::assertFalse($shapes->fitsLength(\str_repeat('x', $length + 1)));
    }

    /**
     * @return iterable<string, array{non-empty-string}>
     */
    public static function unsupportedShapes(): iterable
    {
        yield 'range quantifier' => ['[0-9]{7,8}'];
        yield 'zero count' => ['[0-9]{0}'];
        yield 'leading-zero count' => ['[0-9]{07}'];
        yield 'open quantifier' => ['[0-9]+'];
        yield 'alternation' => ['[0-9]{9}|[A-Z]'];
        yield 'group' => ['(?:[0-9])'];
        yield 'lower-case letter' => ['a[0-9]'];
        yield 'unclosed class' => ['[0-9'];
        yield 'empty class' => ['[]'];
        yield 'negated class' => ['[^0-9]'];
        yield 'negated class after an atom' => ['A[^A-Z]{2}'];
        yield 'escape inside a class' => ['[\\d]'];
        yield 'nested bracket' => ['[[0-9]]'];
        yield 'trailing junk after a valid atom' => ['[0-9]{2}.'];
    }

    /**
     * @param non-empty-string $shape
     */
    #[DataProvider('unsupportedShapes')]
    public function testRefusesAShapeOutsideTheAtomSyntax(string $shape): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A shape is a run of letters, digits and [...] classes with {n}.');

        new NumberShapes($shape);
    }

    private function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z][0-9]{2}', '[0-9]{3}', '[0-9]{5}');
    }
}
