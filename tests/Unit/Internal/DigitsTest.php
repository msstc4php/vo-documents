<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use LogicException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Digits::class)]
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
        $this->expectExceptionMessage('Invalid test number (4 characters).');

        Digits::exactly('12ЗО', 4, 'test number');
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

    public function testNonEmptyReturnsTheDigits(): void
    {
        self::assertSame('12', Digits::nonEmpty('12'));
    }

    public function testNonEmptyRefusesTheEmptyString(): void
    {
        $this->expectException(LogicException::class);

        Digits::nonEmpty('');
    }

    public function testInvalidLengthGivesTheCountOrTooLong(): void
    {
        self::assertSame('Invalid X (3 characters).', Digits::invalidLength('A1é', 'X')->getMessage());
        self::assertSame('Invalid X (40 characters).', Digits::invalidLength(\str_repeat('1', 40), 'X')->getMessage());
        self::assertSame('Invalid X (too long).', Digits::invalidLength(\str_repeat('1', 41), 'X')->getMessage());
    }
}
