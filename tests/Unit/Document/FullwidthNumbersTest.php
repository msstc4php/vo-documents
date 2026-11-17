<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document;

use MSSTC4PHP\ValueObject\Documents\Document\DE\TaxIdentificationNumber;
use MSSTC4PHP\ValueObject\Documents\Document\ES\Dni;
use MSSTC4PHP\ValueObject\Documents\Document\RU\Snils;
use MSSTC4PHP\ValueObject\Documents\Document\US\SocialSecurityNumber;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class FullwidthNumbersTest extends TestCase
{
    public function testSnilsAcceptsFullwidthDigits(): void
    {
        self::assertSame(
            '11223344595',
            Snils::from(
                "\u{FF11}\u{FF11}\u{FF12}-\u{FF12}\u{FF13}\u{FF13}\u{3000}\u{FF14}\u{FF14}" .
                "\u{FF15}\u{FF19}\u{FF15}",
            )->value,
        );
    }

    public function testSocialSecurityNumberAcceptsArabicIndicDigits(): void
    {
        self::assertSame(
            '123456789',
            SocialSecurityNumber::from(
                "\u{0661}\u{0662}\u{0663}\u{0664}\u{0665}\u{0666}\u{0667}\u{0668}\u{0669}",
            )->value,
        );
    }

    public function testSocialSecurityNumberAcceptsFullwidthDigits(): void
    {
        self::assertSame(
            '123456789',
            SocialSecurityNumber::from(
                "\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF19}",
            )->value,
        );
    }

    public function testTaxIdentificationNumberAcceptsFullwidthDigits(): void
    {
        self::assertSame(
            '12345678901',
            TaxIdentificationNumber::from(
                "\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF19}" .
                "\u{FF10}\u{FF11}",
            )->value,
        );
    }

    public function testDniAcceptsFullwidthDigitsAndLetter(): void
    {
        self::assertSame(
            '12345678Z',
            Dni::from("\u{FF11}\u{FF12}\u{FF13}\u{FF14}\u{FF15}\u{FF16}\u{FF17}\u{FF18}\u{FF5A}")->value,
        );
    }
}
