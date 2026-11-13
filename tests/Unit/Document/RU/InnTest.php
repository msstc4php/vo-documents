<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\RU;

use MSSTC4PHP\ValueObject\Documents\Document\RU\Inn;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/RU.md "INN". Format only: the control digits are not verified.
 */
#[CoversClass(Inn::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class InnTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'digits only' => ['500100732259', '500100732259'];
        yield 'spaces' => ['5001 007322 59', '500100732259'];
        yield 'hyphens' => ['5001-007322-59', '500100732259'];
        yield 'tab and NBSP' => ["5001\t007322\u{00A0}59", '500100732259'];
        yield 'second control digit changed' => ['500100732258', '500100732258'];
        yield 'first control digit changed' => ['500100732249', '500100732249'];
        yield 'number digit changed' => ['500100732359', '500100732359'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresDigits(string $input, string $expected): void
    {
        $inn = Inn::from($input);

        self::assertSame($expected, $inn->value);
        self::assertSame($expected, (string) $inn);
        self::assertSame($expected, $inn->jsonSerialize());
    }

    public function testExposesTaxAuthorityRegionCodeFormatsAndMasks(): void
    {
        $inn = Inn::from('500100732259');

        self::assertSame('50', $inn->taxAuthorityRegionCode());
        self::assertSame('500100732259', $inn->formatted());
        self::assertSame('********2259', $inn->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'zeros only' => ['000000000000', 'Invalid INN (zeros only).'];
        yield 'organisation INN length' => ['7707083893', 'Invalid INN (10 characters).'];
        yield 'too long' => ['5001007322590', 'Invalid INN (13 characters).'];
        yield 'letters' => ['50010073225O', 'Invalid INN (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Inn => Inn::from($value), $input, $message);
    }
}
