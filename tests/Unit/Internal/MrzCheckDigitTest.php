<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzCheckDigit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MrzCheckDigit::class)]
final class MrzCheckDigitTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int}>
     */
    public static function specimens(): iterable
    {
        yield 'Part 3 App A example 1, date' => ['520727', 3];
        yield 'Part 3 App A example 2, number with fillers' => ['AB2134<<<', 5];
        yield 'Part 4 passport number' => ['L898902C3', 6];
        yield 'Part 4 birth date' => ['740812', 2];
        yield 'Part 4 expiry date' => ['340415', 9];
        yield 'Part 4 personal number' => ['ZE184226B<<<<<', 1];
        yield 'Part 4 composite' => ['L898902C3674081223404159ZE184226B<<<<<1', 6];
        yield 'Part 5 composite' => ['D231458907<<<<<<<<<<<<<<<74081221204159<<<<<<<<<<<', 6];
        yield 'Part 6 composite' => ['D23145890774081221204159<<<<<<<', 6];
        yield 'all fillers' => ['<<<<<<', 0];
    }

    #[DataProvider('specimens')]
    public function testSpecimensOfDoc9303(string $data, int $digit): void
    {
        self::assertSame($digit, MrzCheckDigit::of($data));
        self::assertTrue(MrzCheckDigit::matches($data, (string) $digit));
        self::assertFalse(MrzCheckDigit::matches($data, (string) (($digit + 1) % 10)));
    }

    public function testNoCharacterOtherThanADigitIsACheckDigit(): void
    {
        self::assertFalse(MrzCheckDigit::matches('<<<<<<', '<'));
        self::assertFalse(MrzCheckDigit::matches('<<<<<<', ''));
        self::assertFalse(MrzCheckDigit::matches('<<<<<<', '00'));
        self::assertFalse(MrzCheckDigit::matches('<<<<<<', 'A'));
    }

    public function testLettersTakeTheValuesTenToThirtyFive(): void
    {
        self::assertSame(0, MrzCheckDigit::of('A'));
        self::assertSame(5, MrzCheckDigit::of('Z'));
    }

    public function testACharacterOutsideTheMrzAlphabetIsALogicError(): void
    {
        $this->expectException(LogicException::class);

        MrzCheckDigit::of('a');
    }
}
