<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Cns;
use MSSTC4PHP\ValueObject\Documents\Internal\Checksum;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/BR.md "CNS": fifteen digits and the ANS `isDvCnsValido` routine (all zeros refused;
 * `validaCns` or `validaCnsProv`). The numbers are synthetic.
 */
#[CoversClass(Cns::class)]
#[UsesClass(Checksum::class)]
#[UsesClass(Digits::class)]
#[UsesClass(Text::class)]
#[UsesClass(Mask::class)]
final class CnsTest extends TestCase
{
    use RefusesWithoutEchoingInput;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validNumbers(): iterable
    {
        yield 'begins with 7 (validaCnsProv)' => ['700000000000005', '700000000000005'];
        yield 'card layout with spaces' => ['700 0000 0000 0005', '700000000000005'];
        yield 'begins with 8 (validaCnsProv)' => ['898000000000002', '898000000000002'];
        yield 'from a PIS, 000 branch (validaCns)' => ['200000000000003', '200000000000003'];
        yield 'from a PIS, 001 branch: check result 10, plus 2 (validaCns)' => ['100000000060018', '100000000060018'];
    }

    #[DataProvider('validNumbers')]
    public function testAccepts(string $input, string $expected): void
    {
        $number = Cns::from($input);

        self::assertSame($expected, $number->value);
        self::assertSame($expected, $number->jsonSerialize());
    }

    public function testMasksKeepingFourDigits(): void
    {
        self::assertSame('***********0005', Cns::from('700000000000005')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
        yield 'sum 109, 10 mod 11' => ['700000000000004', 'Invalid Brazilian CNS checksum.'];
        yield 'fails both ANS routines' => ['123456789012345', 'Invalid Brazilian CNS checksum.'];
        yield '001 branch with the 000 check digit' => ['100000000060010', 'Invalid Brazilian CNS checksum.'];
        yield 'all zeros (isDvCnsValido)' => ['000000000000000', 'Invalid Brazilian CNS checksum.'];
        yield 'seven digits, as the DATASUS error message' => ['1111111', 'Invalid Brazilian CNS (7 characters).'];
        yield 'fourteen digits' => ['70000000000000', 'Invalid Brazilian CNS (14 characters).'];
        yield 'sixteen digits' => ['7000000000000040', 'Invalid Brazilian CNS (16 characters).'];
        yield 'dots are no separator' => ['700.0000.0000.0004', 'Invalid Brazilian CNS (18 characters).'];
        yield 'letter' => ['70000000000000A', 'Invalid Brazilian CNS (invalid character).'];
        yield 'empty' => ['', 'Invalid Brazilian CNS (0 characters).'];
    }

    #[DataProvider('invalidNumbers')]
    public function testRefusesWithoutEchoingTheInput(string $input, string $message): void
    {
        self::assertRefusedWithoutEcho(static fn (string $value): Cns => Cns::from($value), $input, $message);
    }
}
