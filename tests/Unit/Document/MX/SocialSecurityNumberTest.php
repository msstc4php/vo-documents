<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Document\MX\SocialSecurityNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/MX.md "NSS" (IMSS norm 3000-001-007, 5.57); the check digit is not verified.
 */
#[CoversClass(SocialSecurityNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Mask::class)]
#[UsesClass(Text::class)]
final class SocialSecurityNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    public function testAcceptsElevenDigitsWithSeparators(): void
    {
        self::assertSame('12345678901', SocialSecurityNumber::from('12 3456 7890-1')->value);
        self::assertSame('00000000000', SocialSecurityNumber::from('00000000000')->value);
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*******8901', SocialSecurityNumber::from('12345678901')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'ten digits' => ['1234567890', 'Invalid Mexican social security number (wrong length).'];
        yield 'twelve digits' => ['123456789012', 'Invalid Mexican social security number (wrong length).'];
        yield 'letter' => ['1234567890A', 'Invalid Mexican social security number (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): SocialSecurityNumber => SocialSecurityNumber::from($value),
            $input,
            $message,
        );
    }
}
