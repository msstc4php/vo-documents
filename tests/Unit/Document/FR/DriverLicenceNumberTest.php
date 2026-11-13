<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\FR;

use MSSTC4PHP\ValueObject\Documents\Document\FR\DriverLicenceNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/FR.md "Driving licence number".
 */
#[CoversClass(DriverLicenceNumber::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class DriverLicenceNumberTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'twelve digits' => ['130175102345', '130175102345'];
        yield 'with spaces' => ['1301 7510 2345', '130175102345'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresTheDigits(string $input, string $expected): void
    {
        $number = DriverLicenceNumber::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasks(): void
    {
        self::assertSame('********2345', DriverLicenceNumber::from('130175102345')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'eleven digits' => ['13017510234', 'Invalid French driving licence number (11 characters).'];
        yield 'pre-1975 alphanumeric number' => ['AB1234', 'Invalid French driving licence number (6 characters).'];
        yield 'letter' => ['13017510234A', 'Invalid French driving licence number (invalid character).'];
        yield 'empty' => ['', 'Invalid French driving licence number (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(
            static fn (string $value): DriverLicenceNumber => DriverLicenceNumber::from($value),
            $input,
            $message,
        );
    }
}
