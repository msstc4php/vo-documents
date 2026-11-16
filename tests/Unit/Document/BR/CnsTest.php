<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Tests\Unit\Document\BR;

use MSSTC4PHP\ValueObject\Documents\Document\BR\Cns;
use MSSTC4PHP\ValueObject\Documents\Internal\Digits;
use MSSTC4PHP\ValueObject\Documents\Internal\Mask;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Tests\Fixture\RefusesWithoutEchoingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Expectations follow docs/sources/BR.md "CNS": fifteen digits, format only (no check rule of the Ministério da Saúde
 * read), so a number failing the ANS routine is accepted. The numbers are synthetic.
 */
#[CoversClass(Cns::class)]
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
        yield 'begins with 7' => ['700000000000004', '700000000000004'];
        yield 'card layout with spaces' => ['700 0000 0000 0004', '700000000000004'];
        yield 'fails the ANS routine, accepted' => ['123456789012345', '123456789012345'];
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
        self::assertSame('***********0004', Cns::from('700000000000004')->masked());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidNumbers(): iterable
    {
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
