<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\MX;

use MSSTC4PHP\ValueObject\Documents\Document\MX\Rfc;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/MX.md "RFC" (SAT tdCFDI.xsd, t_RFC_PF).
 */
#[CoversClass(Rfc::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
final class RfcTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'plain' => ['HEGJ5612311A2', 'HEGJ5612311A2'];
        yield 'lower case, spaces, hyphens' => ['hegj-561231 1a2', 'HEGJ5612311A2'];
        yield 'final A' => ['HEGJ561231ZZA', 'HEGJ561231ZZA'];
        yield 'eñe, lower case' => ['muñj5612311A2', 'MUÑJ5612311A2'];
        yield 'decomposed eñe' => ["MUN\u{0303}J5612311A2", 'MUÑJ5612311A2'];
        yield 'ampersand' => ['A&BC5612311A2', 'A&BC5612311A2'];
        yield 'generic RFC of the public' => ['XAXX010101000', 'XAXX010101000'];
        yield '30 February (the XSD checks ranges only)' => ['HEGJ5602301A2', 'HEGJ5602301A2'];
    }

    #[DataProvider('validNumbers')]
    public function testAcceptsAndStoresUpperCase(string $input, string $expected): void
    {
        $rfc = Rfc::from($input);

        self::assertSame($expected, $rfc->value);
        self::assertSame($expected, (string) $rfc);
    }

    public function testMasksAllButTheLastFour(): void
    {
        self::assertSame('*********11A2', Rfc::from('HEGJ5612311A2')->masked());
        self::assertSame('*********11A2', Rfc::from('MUÑJ5612311A2')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'legal person, 12 characters' => ['ABC5612311A2', 'Invalid Mexican RFC (wrong length).'];
        yield 'too long' => ['HEGJ5612311A23', 'Invalid Mexican RFC (wrong length).'];
        yield 'empty' => ['', 'Invalid Mexican RFC (wrong length).'];
        yield 'digit among the letters' => ['HEG15612311A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'month 13' => ['HEGJ5613311A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'month 00' => ['HEGJ5600311A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'day 00' => ['HEGJ5612001A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'day 32' => ['HEGJ5612321A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'final letter other than A' => ['HEGJ5612311AB', 'Invalid Mexican RFC (invalid character).'];
        yield 'symbol in the homoclave' => ['HEGJ561231&A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'other accented letter' => ['HÉGJ5612311A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'sharp s, not read as SS' => ['ßABC5612311A2', 'Invalid Mexican RFC (invalid character).'];
        yield 'fi ligature, not read as FI' => ["\u{FB01}ABC5612311A2", 'Invalid Mexican RFC (invalid character).'];
        yield 'dotless i, not read as I' => ["\u{0131}ABC5612311A2", 'Invalid Mexican RFC (invalid character).'];
        yield 'long s, not read as S' => ["\u{017F}ABC5612311A2", 'Invalid Mexican RFC (invalid character).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Rfc => Rfc::from($value), $input, $message);
    }
}
