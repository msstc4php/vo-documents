<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Internal\UnicodeText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnicodeText::class)]
final class UnicodeTextTest extends TestCase
{
    public function testNormalizeNameComposesTrimsAndCollapsesWhitespace(): void
    {
        self::assertSame('Андрей Петров', UnicodeText::normalizeName("  Андреи\u{0306}\u{00A0}  Петров "));
    }

    public function testNormalizeNameKeepsLetterCase(): void
    {
        self::assertSame('van der Berg', UnicodeText::normalizeName("van\tder  Berg"));
    }

    public function testNormalizeNameRejectsInvalidUtf8WithoutEchoingInput(): void
    {
        try {
            UnicodeText::normalizeName("ab\xC3\x28cd");
            self::fail('Expected exception.');
        } catch (InvalidPersonNameException $e) {
            self::assertStringNotContainsString('ab', $e->getMessage());
        }
    }

    public function testIsInScriptAcceptsPureCyrillic(): void
    {
        self::assertTrue(UnicodeText::isInScript('Иван', '\p{Cyrillic}'));
    }

    public function testIsInScriptRejectsLatinLookAlike(): void
    {
        self::assertFalse(UnicodeText::isInScript("Ив\u{0061}н", '\p{Cyrillic}'));
    }

    public function testIsInScriptRejectsEmptyText(): void
    {
        self::assertFalse(UnicodeText::isInScript('', '\p{Cyrillic}'));
    }
}
