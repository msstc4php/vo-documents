<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\InternationalPassportNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "International passport". The source has no check digit and no example number, so the
 * value is checked by its shape only.
 */
#[CoversClass(InternationalPassportNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
final class InternationalPassportNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'formatted' => ['75 1234567', '751234567'];
        yield 'MRZ form' => ['751234567', '751234567'];
        yield 'NBSP' => ["75\u{00A0}1234567", '751234567'];
        yield 'hyphen and tab' => ["75-\t1234567", '751234567'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = InternationalPassportNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $number = InternationalPassportNumber::from('751234567');

        self::assertSame('75 1234567', $number->formatted());
        self::assertSame('** ***4567', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'too short' => ['75 123456', 'Invalid Russian international passport number (8 characters).'];
        yield 'internal passport length' => ['45 08 123456', 'Invalid Russian international passport number (10 characters).'];
        yield 'letters' => ['75 123456A', 'Invalid Russian international passport number (9 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): InternationalPassportNumber => InternationalPassportNumber::from($value),
            $input,
            $message,
        );
    }
}
