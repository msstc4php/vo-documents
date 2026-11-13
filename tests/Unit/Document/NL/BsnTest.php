<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\NL;

use MSSTC4PHP\ValueObject\Documents\Document\NL\Bsn;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberFormat;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/NL.md "BSN". `999990068`, `999990019`, `999990160`, `999994396` and `999994931`
 * are RvIG test-set numbers ("Nummer is een BSN"); `123456789` is its "Nummer is geen BSN" case.
 */
#[CoversClass(Bsn::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(NumberFormat::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class BsnTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'RvIG test number' => ['999990068', '999990068'];
        yield 'RvIG test number 2' => ['999990019', '999990019'];
        yield 'RvIG test number 3' => ['999990160', '999990160'];
        yield 'RvIG test number 4' => ['999994396', '999994396'];
        yield 'RvIG test number 5' => ['999994931', '999994931'];
        yield 'last digit 9 (weight -1)' => ['111222333', '111222333'];
        yield 'leading zero kept' => ['012345672', '012345672'];
        yield 'eight digits read with a leading zero' => ['12345672', '012345672'];
        yield 'spaces and NBSP' => ["9999 900\u{00A0}68", '999990068'];
        yield 'hyphens' => ['999-990-068', '999990068'];
        yield 'zeros pass the elfproef, no range is published' => ['000000000', '000000000'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = Bsn::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, (string) $number);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasks(): void
    {
        self::assertSame('*****0068', Bsn::from('999990068')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        $checksum = 'Invalid Dutch BSN checksum.';

        yield 'RvIG "geen BSN" example' => ['123456789', $checksum];
        yield 'last digit changed' => ['999990069', $checksum];
        yield 'transposed digits' => ['999990086', $checksum];
        yield 'eight digits failing' => ['12345678', $checksum];
        yield 'negative sum' => ['000000009', $checksum];
        yield 'seven digits' => ['1234567', 'Invalid Dutch BSN (7 characters).'];
        yield 'ten digits' => ['9999900680', 'Invalid Dutch BSN (10 characters).'];
        yield 'letter' => ['99999006A', 'Invalid Dutch BSN (invalid character).'];
        yield 'dot' => ['9999.9006', 'Invalid Dutch BSN (invalid character).'];
        yield 'empty' => ['', 'Invalid Dutch BSN (0 characters).'];
        yield 'far too long' => [\str_repeat('9', 50), 'Invalid Dutch BSN (too long).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Bsn => Bsn::from($value), $input, $message);
    }
}
