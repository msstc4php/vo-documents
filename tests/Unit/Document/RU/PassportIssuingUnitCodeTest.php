<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\PassportIssuingUnitCode;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "Passport issuing unit code". The source has no check digit and no example
 * number, so the value is checked by its shape only.
 */
#[CoversClass(PassportIssuingUnitCode::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class PassportIssuingUnitCodeTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'formatted' => ['772-078', '772078'];
        yield 'MRZ form' => ['772078', '772078'];
        yield 'space' => ['772 078', '772078'];
        yield 'NBSP' => ["772\u{00A0}078", '772078'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $number = PassportIssuingUnitCode::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testFormatsAndMasks(): void
    {
        $number = PassportIssuingUnitCode::from('772078');

        self::assertSame('772-078', $number->formatted());
        self::assertSame('**2-078', $number->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'too short' => ['772-07', 'Invalid Russian passport issuing unit code (5 characters).'];
        yield 'too long' => ['772-0789', 'Invalid Russian passport issuing unit code (7 characters).'];
        yield 'letters' => ['77A-078', 'Invalid Russian passport issuing unit code (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): PassportIssuingUnitCode => PassportIssuingUnitCode::from($value),
            $input,
            $message,
        );
    }
}
