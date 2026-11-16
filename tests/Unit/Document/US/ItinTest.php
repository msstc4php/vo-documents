<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\US;

use MSSTC4PHP\ValueObject\Documents\Document\US\Itin;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/US.md "ITIN" (IRM 3.13.5.22: first digit 9; fourth and fifth digits 50-65, 70-88,
 * 90-92, 94-99). Every bound of every range is tested.
 */
#[CoversClass(Itin::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class ItinTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string}>
     */
    public static function validMiddleDigits(): iterable
    {
        foreach (['50', '57', '65', '70', '88', '90', '92', '94', '99'] as $middle) {
            yield $middle => ['912-' . $middle . '-1234'];
        }
    }

    #[DataProvider('validMiddleDigits')]
    public function testAcceptsTheIrsRanges(string $input): void
    {
        self::assertSame(\str_replace('-', '', $input), Itin::from($input)->value);
    }

    public function testAcceptsSeparators(): void
    {
        self::assertSame('900700000', Itin::from("900 70\u{00A0}0000")->value);
    }

    public function testFormatsAndMasksAsTheIrsTruncatedForm(): void
    {
        $number = Itin::from('912701234');

        self::assertSame('912-70-1234', $number->formatted());
        self::assertSame('***-**-1234', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $middle = 'Invalid American individual taxpayer identification number (fourth and fifth digits).';

        foreach (['00', '49', '66', '69', '89', '93'] as $digits) {
            yield 'middle ' . $digits => ['912-' . $digits . '-1234', $middle];
        }

        yield 'an SSN' => [
            '123-45-6789',
            'Invalid American individual taxpayer identification number (first digit).',
        ];
        yield 'first digit 8' => [
            '812-70-1234',
            'Invalid American individual taxpayer identification number (first digit).',
        ];
        yield 'letter' => [
            '912-70-123X',
            'Invalid American individual taxpayer identification number (invalid character).',
        ];
        yield 'ten digits' => [
            '912-70-12345',
            'Invalid American individual taxpayer identification number (10 characters).',
        ];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Itin => Itin::from($value), $input, $message);
    }
}
