<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\GB;

use MSSTC4PHP\ValueObject\Documents\Document\GB\NorthernIrelandDriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/GB.md "Driving licence number, Northern Ireland"; DVA publishes no example number.
 */
#[CoversClass(NorthernIrelandDriverLicenceNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class NorthernIrelandDriverLicenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'compact' => ['12345678', '12345678'];
        yield 'leading zeros kept' => ['00123456', '00123456'];
        yield 'spaces and NBSP' => ["1234\u{00A0}5678", '12345678'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = NorthernIrelandDriverLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksAllButTheLastThreeDigits(): void
    {
        self::assertSame('*****678', NorthernIrelandDriverLicenceNumber::from('12345678')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'seven digits' => ['1234567', 'Invalid Northern Ireland driving licence number (7 characters).'];
        yield 'nine digits' => ['123456789', 'Invalid Northern Ireland driving licence number (9 characters).'];
        yield 'Great Britain number' => [
            'MORGA657054SM9IJ',
            'Invalid Northern Ireland driving licence number (16 characters).',
        ];
        yield 'letter' => ['A1234567', 'Invalid Northern Ireland driving licence number (invalid character).'];
        yield 'empty' => ['', 'Invalid Northern Ireland driving licence number (0 characters).'];
        yield 'far too long' => [\str_repeat('1', 50), 'Invalid Northern Ireland driving licence number (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            NorthernIrelandDriverLicenceNumber::from(...),
            $input,
            $message,
        );
    }
}
