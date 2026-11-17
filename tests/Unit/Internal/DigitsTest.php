<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Digits::class)]
#[UsesClass(Text::class)]
final class DigitsTest extends TestCase
{
    public function testStripRemovesDefaultSeparators(): void
    {
        self::assertSame('11223344595', Digits::strip("112 233-445\u{00A0}95"));
    }

    public function testStripRemovesTabs(): void
    {
        self::assertSame('123', Digits::strip("1\t2\t3"));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function dashLikeSeparators(): iterable
    {
        yield 'fullwidth hyphen-minus U+FF0D' => ["\u{FF0D}"];
        yield 'halfwidth prolonged sound mark U+FF70' => ["\u{FF70}"];
        yield 'katakana-hiragana prolonged sound mark U+30FC' => ["\u{30FC}"];
        yield 'hyphen U+2010' => ["\u{2010}"];
        yield 'non-breaking hyphen U+2011' => ["\u{2011}"];
        yield 'minus sign U+2212' => ["\u{2212}"];
    }

    #[DataProvider('dashLikeSeparators')]
    public function testStripRemovesEveryDashLikeCharacter(string $separator): void
    {
        self::assertSame('1234', Digits::strip('12' . $separator . '34'));
    }

    public function testStripKeepsLettersAndHonoursCustomSeparators(): void
    {
        self::assertSame('1a-2', Digits::strip('1a-2.', '.'));
    }

    public function testStripRemovesLineBreaksAndNarrowSpaces(): void
    {
        self::assertSame('1234', Digits::strip("1\r\n2\u{2007}3\u{202F}4"));
    }

    public function testStripKeepsLettersSharingBytesWithMultibyteSeparators(): void
    {
        self::assertSame("1\u{0420}2", Digits::strip("1\u{0420}\u{00A0}2"));
    }

    public function testFoldMapsFullwidthDigitsAndLettersToAscii(): void
    {
        self::assertSame('1234', Digits::fold("\u{FF11}\u{FF12}\u{FF13}\u{FF14}"));
        self::assertSame('AB', Digits::fold("\u{FF41}\u{FF42}"));
        self::assertSame('AZ09', Digits::fold("\u{FF21}\u{FF3A}\u{FF10}\u{FF19}"));
        self::assertSame('ZA', Digits::fold("\u{FF5A}\u{FF21}"));
    }

    public function testFoldMapsArabicIndicDigitsToAscii(): void
    {
        self::assertSame('123', Digits::fold("\u{0661}\u{0662}\u{0663}"));
        self::assertSame('123', Digits::fold("\u{06F1}\u{06F2}\u{06F3}"));
        self::assertSame('09', Digits::fold("\u{0660}\u{0669}"));
        self::assertSame('09', Digits::fold("\u{06F0}\u{06F9}"));
    }

    public function testFoldLeavesOtherCharactersAndInvalidUtf8Alone(): void
    {
        self::assertSame("ab-1\u{0420}", Digits::fold("ab-1\u{0420}"));
        self::assertSame("\xFF", Digits::fold("\xFF"));
    }

    public function testStripFoldsAndRemovesTheIdeographicSpace(): void
    {
        self::assertSame('1234', Digits::strip("\u{FF11}\u{FF12}\u{3000}\u{FF13}\u{FF14}"));
    }

    public function testUpperCasedOfLengthFoldsBeforeCounting(): void
    {
        self::assertSame(
            'AB',
            Digits::upperCasedOfLength("\u{FF41}\u{3000}\u{FF42}", static fn (int $n): bool => $n === 2, 'test number'),
        );
    }

    public function testStrippedLengthFoldsBeforeCounting(): void
    {
        self::assertSame(3, Digits::strippedLength("\u{0661}\u{3000}\u{0662}\u{06F3}"));
    }

    public function testExactlyReturnsTheDigits(): void
    {
        self::assertSame('0123', Digits::exactly(' 01-23 ', 4, 'test number'));
    }

    public function testExactlyRefusesAnotherLengthWithTheCharacterCount(): void
    {
        $this->expectException(InvalidDocumentNumberException::class);
        $this->expectExceptionMessage('Invalid test number (3 characters).');

        Digits::exactly('123', 4, 'test number');
    }

    public function testExactlyReportsTheCountUpToTheCap(): void
    {
        $this->expectExceptionMessage('Invalid test number (40 characters).');

        Digits::exactly(\str_repeat('1', 40), 4, 'test number');
    }

    public function testExactlySaysTooLongAboveTheCap(): void
    {
        $this->expectExceptionMessage('Invalid test number (too long).');

        Digits::exactly(\str_repeat('1', 41), 4, 'test number');
    }

    public function testExactlyCountsMultibyteCharactersOnce(): void
    {
        $this->expectExceptionMessage('Invalid test number (5 characters).');

        Digits::exactly('12ЗО3', 4, 'test number');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function rightLengthWithANonDigit(): iterable
    {
        yield 'Cyrillic letters at the end' => ['12ЗО'];
        yield 'Latin letter first' => ['A234'];
        yield 'letter inside' => ['12A4'];
    }

    #[DataProvider('rightLengthWithANonDigit')]
    public function testExactlySaysInvalidCharacterForTheRightLength(string $input): void
    {
        $this->expectException(InvalidDocumentNumberException::class);
        $this->expectExceptionMessage('Invalid test number (invalid character).');

        Digits::exactly($input, 4, 'test number');
    }

    public function testExactlyRefusesEmptyInput(): void
    {
        $this->expectExceptionMessage('Invalid test number (0 characters).');

        Digits::exactly(' - ', 1, 'test number');
    }

    public function testExactlyRefusesATrailingControlCharacter(): void
    {
        $this->expectException(InvalidDocumentNumberException::class);

        Digits::exactly("1234\u{0000}", 4, 'test number');
    }

    public function testInvalidLengthGivesTheCountOrTooLong(): void
    {
        self::assertSame('Invalid X (3 characters).', Digits::invalidLength('A1é', 'X')->getMessage());
        self::assertSame('Invalid X (40 characters).', Digits::invalidLength(\str_repeat('1', 40), 'X')->getMessage());
        self::assertSame('Invalid X (too long).', Digits::invalidLength(\str_repeat('1', 41), 'X')->getMessage());
    }

    public function testUpperCasedOfLengthStripsUpperCasesAsciiOnlyAndPassesTheCharacterCount(): void
    {
        $seen = [];
        $allow = static function (int $length) use (&$seen): bool {
            $seen[] = $length;

            return true;
        };

        self::assertSame("A\u{00E9}1*", Digits::upperCasedOfLength(" a-\u{00E9}\t1* ", $allow, 'X'));
        self::assertSame([4], $seen);
    }

    public function testUpperCasedOfLengthRefusesALengthTheCallerRefuses(): void
    {
        $this->expectException(InvalidDocumentNumberException::class);
        $this->expectExceptionMessage('Invalid X (2 characters).');

        Digits::upperCasedOfLength('a-b', static fn (int $length): bool => $length !== 2, 'X');
    }

    public function testStrippedLengthCountsCharactersWithoutSeparators(): void
    {
        self::assertSame(3, Digits::strippedLength(" \u{00E9}-1\u{00A0}2 "));
        self::assertSame(0, Digits::strippedLength(' - '));
    }
}
