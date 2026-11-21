<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Internal;

use MSSTC4PHP\ValueObject\Documents\Internal\MrzCheckDigit;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzDocumentNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MrzDocumentNumber::class)]
#[UsesClass(MrzCheckDigit::class)]
final class MrzDocumentNumberTest extends TestCase
{
    public function testAShortNumberWithItsCheckDigit(): void
    {
        self::assertSame(
            ['number' => 'D23145890', 'optional' => '<<<<<<<'],
            MrzDocumentNumber::resolve('D23145890', '7', '<<<<<<<', true),
        );
    }

    public function testFillersAtTheEndOfTheNumberAreRemoved(): void
    {
        self::assertSame(
            ['number' => 'AB2134', 'optional' => ''],
            MrzDocumentNumber::resolve('AB2134<<<', '5', '', false),
        );
    }

    public function testAWrongCheckDigitIsRefused(): void
    {
        self::assertNull(MrzDocumentNumber::resolve('D23145890', '8', '<<<<<<<', true));
        self::assertNull(MrzDocumentNumber::resolve('D23145890', 'X', '<<<<<<<', true));
    }

    public function testALongNumberContinuesInTheOptionalData(): void
    {
        self::assertSame(
            ['number' => 'ABC12345678', 'optional' => '<<<<<<<<<<<'],
            MrzDocumentNumber::resolve('ABC123456', '<', '783<<<<<<<<<<<<', true),
        );
    }

    public function testAnOverflowOfOneCharacter(): void
    {
        $check = (string) MrzCheckDigit::of('ABC1234567');

        self::assertSame(
            ['number' => 'ABC1234567', 'optional' => '<<<<<<<<<<<<'],
            MrzDocumentNumber::resolve('ABC123456', '<', '7' . $check . '<<<<<<<<<<<<<', true),
        );
    }

    public function testTheSplitWithOnlyFillersAfterItIsPreferred(): void
    {
        self::assertSame(
            ['number' => 'ABC123456123<345', 'optional' => '<<<<<<'],
            MrzDocumentNumber::resolve('ABC123456', '<', '123<3454<<<<<<<', true),
        );
    }

    public function testWithoutSuchASplitTheFirstValidOneWins(): void
    {
        self::assertSame(
            ['number' => 'ABC12345612', 'optional' => '3454<XYZ<<<'],
            MrzDocumentNumber::resolve('ABC123456', '<', '123<3454<XYZ<<<', true),
        );
    }

    public function testALongNumberIsRefusedWhereTheFormatHasNone(): void
    {
        self::assertNull(MrzDocumentNumber::resolve('ABC123456', '<', '783<<<<<<<<<<<<', false));
    }

    public function testALongNumberWithAWrongCheckDigitIsRefused(): void
    {
        self::assertNull(MrzDocumentNumber::resolve('ABC123456', '<', '784<<<<<<<<<<<<', true));
        self::assertNull(MrzDocumentNumber::resolve('ABC123456', '<', '<<<<<<<<<<<<<<<', true));
    }

    public function testTheCheckDigitNeedsAFillerAfterIt(): void
    {
        self::assertNull(MrzDocumentNumber::resolve('ABC123456', '<', '7830<<<<<<<<<<<', true));
    }

    public function testDataAfterTheCheckDigitStaysOptionalData(): void
    {
        $number = MrzDocumentNumber::resolve('ABC123456', '<', '783<<XYZ<<<<<<<', true);

        self::assertSame('ABC12345678', $number['number'] ?? null);
        self::assertSame('<XYZ<<<<<<<', $number['optional'] ?? null);
    }

    public function testALongNumberFillsTheOptionalFieldOfATd2(): void
    {
        $overflow = '12345';
        $check = (string) MrzCheckDigit::of('ABC123456' . $overflow);

        self::assertSame(
            ['number' => 'ABC12345612345', 'optional' => ''],
            MrzDocumentNumber::resolve('ABC123456', '<', $overflow . $check . '<', true),
        );
    }

    public function testTheNumberMayEndInTheLastPositionBeforeTheFiller(): void
    {
        $check = (string) MrzCheckDigit::of('ABC1234561234567890123');

        self::assertNotNull(MrzDocumentNumber::resolve('ABC123456', '<', '1234567890123' . $check . '<', true));
    }
}
